<?php

namespace App\Http\Controllers;

use App\Services\AlertaVencimentoService;
use App\Services\LoteService;
use App\Services\ModuloService;
use App\Services\RelatorioService;
use App\Services\SaidaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'relatorios')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão para relatórios.']);
        }

        $relatorios = ModuloService::catalogoRelatorios();
        $ehAdmin = in_array($perfil, ['superuser', 'admin', 'gestor'], true);
        $clinicas = $ehAdmin
            ? DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get(['id', 'nome'])
            : collect();

        return view('relatorios.index', compact('relatorios', 'clinicas', 'ehAdmin'));
    }

    public function exportar(Request $request, string $tipo)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'relatorios')) {
            abort(403);
        }

        $formato = $request->query('formato', 'csv');
        $clinicaId = RelatorioService::escopoClinica($request);
        $de = $request->query('de');
        $ate = $request->query('ate');

        return match ($tipo) {
            'estoque_geral', 'estoque_unidade' => $this->exportarEstoque($clinicaId, $formato, $tipo),
            'vencendo' => $this->exportarVencimento($clinicaId, 'vencendo', $formato),
            'vencidos' => $this->exportarVencimento($clinicaId, 'vencido', $formato),
            'dispensa_paciente' => $this->exportarDispensaPaciente($clinicaId, $de, $ate),
            'dispensa_medicamento' => $this->exportarDispensaMedicamento($clinicaId, $de, $ate),
            'transferencias' => $this->exportarTransferencias($clinicaId, $de, $ate),
            'consumo_mensal' => $this->exportarConsumoMensal($clinicaId),
            'consumo_anual' => $formato === 'pdf'
                ? redirect()->route('relatorios.imprimir', array_filter([
                    'tipo' => 'consumo_anual',
                    'clinica_id' => $clinicaId ?: null,
                    'de' => $de,
                    'ate' => $ate,
                    'ano' => $request->query('ano'),
                ]))
                : $this->exportarConsumoAnual($request, $clinicaId),
            'movimentacoes' => $this->exportarMovimentacoes($clinicaId),
            default => redirect()->route('relatorios.index')->withErrors(['error' => 'Relatório inválido.']),
        };
    }

    public function imprimir(Request $request, string $tipo)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'relatorios')) {
            abort(403);
        }

        $clinicaId = RelatorioService::escopoClinica($request);

        if ($tipo === 'estoque_geral' || $tipo === 'estoque_unidade') {
            $dados = RelatorioService::estoqueGeral($clinicaId ?: null);
            $titulo = $clinicaId ? 'Estoque por unidade' : 'Estoque geral';

            return view('relatorios.print.estoque', compact('dados', 'titulo'));
        }

        if ($tipo === 'vencendo' || $tipo === 'vencidos') {
            $alertaTipo = $tipo === 'vencidos' ? 'vencido' : 'vencendo';
            $dados = AlertaVencimentoService::listar($clinicaId ?: null, $alertaTipo);
            $titulo = $tipo === 'vencidos' ? 'Medicamentos vencidos' : 'Medicamentos vencendo';

            return view('relatorios.print.vencimento', compact('dados', 'titulo'));
        }

        if ($tipo === 'consumo_anual') {
            [$anoInicio, $anoFim] = RelatorioService::intervaloAnualRequest($request);
            $resumo = RelatorioService::consumoAnualResumo($clinicaId ?: null, $anoInicio, $anoFim);
            $detalhe = RelatorioService::consumoAnual($clinicaId ?: null, $anoInicio, $anoFim);
            $titulo = $anoInicio === $anoFim
                ? "Consumo anual {$anoInicio}"
                : "Consumo anual {$anoInicio}–{$anoFim}";

            return view('relatorios.print.consumo-anual', compact('resumo', 'detalhe', 'titulo', 'anoInicio', 'anoFim'));
        }

        abort(404);
    }

    private function exportarEstoque(int $clinicaId, string $formato, string $tipo)
    {
        if ($formato === 'pdf') {
            return redirect()->route('relatorios.imprimir', ['tipo' => $tipo, 'clinica_id' => $clinicaId ?: null]);
        }

        $rows = RelatorioService::estoqueGeral($clinicaId ?: null)->map(fn ($r) => [
            $r->unidade,
            $r->codigo,
            $r->medicamento,
            $r->dosagem,
            $r->un_medida,
            $r->quantidade,
            $r->estoque_minimo,
        ])->all();

        return RelatorioService::exportarCsv(
            'estoque_' . date('Y-m-d') . '.csv',
            ['Unidade', 'Código', 'Medicamento', 'Dosagem', 'Unidade medida', 'Quantidade', 'Estoque mínimo'],
            $rows
        );
    }

    private function exportarVencimento(int $clinicaId, string $alertaTipo, string $formato)
    {
        if ($formato === 'pdf') {
            $tipo = $alertaTipo === 'vencido' ? 'vencidos' : 'vencendo';

            return redirect()->route('relatorios.imprimir', ['tipo' => $tipo, 'clinica_id' => $clinicaId ?: null]);
        }

        $rows = AlertaVencimentoService::listar($clinicaId ?: null, $alertaTipo)->map(fn ($r) => [
            $r->clinica,
            $r->codigo,
            $r->medicamento,
            $r->lote ?: '—',
            $r->validade_fmt,
            $r->quantidade_entrada,
            $r->status === 'vencido' ? 'Vencido' : 'Vencendo',
        ])->all();

        return RelatorioService::exportarCsv(
            'validade_' . $alertaTipo . '_' . date('Y-m-d') . '.csv',
            ['Unidade', 'Código', 'Medicamento', 'Lote', 'Validade', 'Qtd entrada', 'Status'],
            $rows
        );
    }

    private function exportarDispensaPaciente(int $clinicaId, ?string $de, ?string $ate)
    {
        $rows = RelatorioService::dispensasPorPaciente($clinicaId ?: null, $de, $ate)->map(fn ($r) => [
            $r->paciente,
            $r->cpf ? SaidaService::formatarCpf($r->cpf) : '',
            $r->cns ?: '',
            $r->unidade,
            $r->medicamento,
            $r->quantidade,
            $r->data_saida,
            $r->prescritor ?: '',
            $r->numero_solicitacao ?: '',
        ])->all();

        return RelatorioService::exportarCsv(
            'dispensa_paciente_' . date('Y-m-d') . '.csv',
            ['Paciente', 'CPF', 'CNS', 'Unidade', 'Medicamento', 'Qtd', 'Data', 'Prescritor', 'Receita/Solicitação'],
            $rows
        );
    }

    private function exportarDispensaMedicamento(int $clinicaId, ?string $de, ?string $ate)
    {
        $rows = RelatorioService::dispensasPorMedicamento($clinicaId ?: null, $de, $ate)->map(fn ($r) => [
            $r->codigo,
            $r->medicamento,
            $r->unidade,
            $r->total,
            $r->qtd_registros,
        ])->all();

        return RelatorioService::exportarCsv(
            'dispensa_medicamento_' . date('Y-m-d') . '.csv',
            ['Código', 'Medicamento', 'Unidade', 'Total dispensado', 'Registros'],
            $rows
        );
    }

    private function exportarTransferencias(int $clinicaId, ?string $de, ?string $ate)
    {
        $rows = RelatorioService::transferencias($clinicaId ?: null, $de, $ate)->map(fn ($r) => [
            $r->codigo_nota,
            $r->status,
            $r->origem,
            $r->destino,
            $r->enviado_em,
            $r->recebido_em ?: '',
        ])->all();

        return RelatorioService::exportarCsv(
            'transferencias_' . date('Y-m-d') . '.csv',
            ['Código nota', 'Status', 'Origem', 'Destino', 'Envio', 'Recebimento'],
            $rows
        );
    }

    private function exportarConsumoMensal(int $clinicaId)
    {
        $rows = RelatorioService::consumoMensal($clinicaId ?: null)->map(fn ($r) => [
            $r->mes,
            $r->unidade,
            $r->total,
        ])->all();

        return RelatorioService::exportarCsv(
            'consumo_mensal_' . date('Y-m-d') . '.csv',
            ['Mês', 'Unidade', 'Total dispensado'],
            $rows
        );
    }

    private function exportarConsumoAnual(Request $request, int $clinicaId)
    {
        [$anoInicio, $anoFim] = RelatorioService::intervaloAnualRequest($request);

        $rows = RelatorioService::consumoAnual($clinicaId ?: null, $anoInicio, $anoFim)->map(fn ($r) => [
            $r->ano,
            $r->unidade,
            $r->codigo,
            $r->medicamento,
            $r->dosagem ?: '',
            $r->total,
            $r->qtd_registros,
        ])->all();

        $suffix = $anoInicio === $anoFim ? (string) $anoInicio : "{$anoInicio}-{$anoFim}";

        return RelatorioService::exportarCsv(
            'consumo_anual_' . $suffix . '.csv',
            ['Ano', 'Unidade', 'Código', 'Medicamento', 'Dosagem', 'Total dispensado', 'Registros'],
            $rows
        );
    }

    private function exportarMovimentacoes(int $clinicaId)
    {
        LoteService::inicializar();

        $q = DB::table('estoque_movimentacoes as mv')
            ->join('medicamentos as m', 'm.id', '=', 'mv.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 'mv.clinica_id')
            ->leftJoin('estoque_lotes as l', 'l.id', '=', 'mv.lote_id')
            ->orderByDesc('mv.criado_em')
            ->limit(5000);

        if ($clinicaId > 0) {
            $q->where('mv.clinica_id', $clinicaId);
        }

        $rows = $q->get(['mv.tipo', 'c.nome as unidade', 'm.nome as medicamento', 'l.lote', 'mv.quantidade', 'mv.saldo_lote_apos', 'mv.criado_em'])
            ->map(fn ($r) => [$r->criado_em, $r->tipo, $r->unidade, $r->medicamento, $r->lote ?: '', $r->quantidade, $r->saldo_lote_apos ?? ''])
            ->all();

        return RelatorioService::exportarCsv(
            'movimentacoes_' . date('Y-m-d') . '.csv',
            ['Data', 'Tipo', 'Unidade', 'Medicamento', 'Lote', 'Quantidade', 'Saldo lote após'],
            $rows
        );
    }
}

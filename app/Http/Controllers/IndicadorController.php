<?php

namespace App\Http\Controllers;

use App\Services\IndicadorService;
use App\Services\ModuloService;
use App\Services\PerfilService;
use App\Services\RelatorioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndicadorController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'relatorios')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        [$mes, $ano] = $this->periodoRequest($request);
        $ehAdmin = PerfilService::ehAdmin($perfil);
        $clinicaId = $ehAdmin
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $indicadores = IndicadorService::mensal($clinicaId ?: null, $mes, $ano);
        $historico = IndicadorService::historico($clinicaId ?: null, 6);
        $clinicas = $ehAdmin
            ? DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get()
            : collect();

        return view('indicadores.index', compact('indicadores', 'historico', 'clinicas', 'clinicaId', 'ehAdmin', 'mes', 'ano'));
    }

    public function exportar(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'relatorios')) {
            abort(403);
        }

        $ehAdmin = PerfilService::ehAdmin($perfil);
        $clinicaId = $ehAdmin
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $rows = IndicadorService::historico($clinicaId ?: null, 12)->map(fn ($r) => [
            $r->periodo_label,
            $r->entradas_unidades,
            $r->saidas_unidades,
            $r->pacientes_atendidos,
            $r->transferencias_enviadas,
            $r->solicitacoes_criadas,
        ])->all();

        return RelatorioService::exportarCsv(
            'indicadores_mensais_' . date('Y-m-d') . '.csv',
            ['Período', 'Entradas (un.)', 'Dispensas (un.)', 'Pacientes', 'Transferências', 'Solicitações'],
            $rows
        );
    }

    public function imprimir(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'relatorios')) {
            abort(403);
        }

        [$mes, $ano] = $this->periodoRequest($request);
        $ehAdmin = PerfilService::ehAdmin($perfil);
        $clinicaId = $ehAdmin
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $indicadores = IndicadorService::mensal($clinicaId ?: null, $mes, $ano);
        $historico = IndicadorService::historico($clinicaId ?: null, 6);
        $titulo = 'Indicadores gerenciais — ' . $indicadores['periodo_label'];

        return view('indicadores.print', compact('indicadores', 'historico', 'titulo'));
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function periodoRequest(Request $request): array
    {
        $mes = (int) $request->query('mes', (int) today()->month);
        $ano = (int) $request->query('ano', (int) today()->year);
        $mes = max(1, min(12, $mes));
        $ano = max(2000, min(2100, $ano));

        return [$mes, $ano];
    }
}

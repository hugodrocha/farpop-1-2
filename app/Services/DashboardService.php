<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public static function visaoGlobal(string $perfil, int $clinicaUsuarioId): bool
    {
        if (PerfilService::ehAdmin($perfil)) {
            return true;
        }

        $centralId = EstoqueService::getCentralId();

        return $centralId && $clinicaUsuarioId === $centralId;
    }

    public static function dadosGlobais(): array
    {
        $pctVermelho = (int) ConfigService::get('alerta_vermelho_percentual', '50');
        $pctAmarelo = (int) ConfigService::get('alerta_amarelo_percentual', '100');

        $criticos = DB::selectOne("
            SELECT SUM(CASE WHEN m.estoque_minimo>0 AND e.quantidade <= (m.estoque_minimo * ? / 100) THEN 1 ELSE 0 END) AS t
            FROM estoque e INNER JOIN medicamentos m ON m.id=e.medicamento_id
        ", [$pctVermelho]);

        $amarelos = DB::selectOne("
            SELECT SUM(CASE WHEN m.estoque_minimo>0 AND e.quantidade <= (m.estoque_minimo * ? / 100) AND e.quantidade > (m.estoque_minimo * ? / 100) THEN 1 ELSE 0 END) AS t
            FROM estoque e INNER JOIN medicamentos m ON m.id=e.medicamento_id
        ", [$pctAmarelo, $pctVermelho]);

        return [
            'clinicas' => DB::table('clinicas')->where('ativo', 1)->count(),
            'medicamentos' => DB::table('medicamentos')->where('ativo', 1)->count(),
            'estoque' => (int) DB::table('estoque')->sum('quantidade'),
            'pacientes' => DB::table('pacientes')->count(),
            'usuarios' => DB::table('usuarios')->count(),
            'entradas_hoje' => DB::table('entradas_medicamentos')->whereDate('data_entrada', today())->count(),
            'saidas_hoje' => DB::table('saidas_medicamentos')->whereDate('data_saida', today())->count(),
            'qtd_saidas_hoje' => (int) DB::table('saidas_medicamentos')->whereDate('data_saida', today())->sum('quantidade'),
            'pac_atendidos_hoje' => DB::table('saidas_medicamentos')->whereDate('data_saida', today())->distinct('paciente_id')->count('paciente_id'),
            'bloqueios_hoje' => DB::table('auditoria')->where('acao', 'BLOQUEIO_SAIDA')->whereDate('criado_em', today())->count(),
            'criticos' => (int) ($criticos->t ?? 0),
            'amarelos' => (int) ($amarelos->t ?? 0),
            'transf_pendentes' => DB::table('transferencias')->where('status', 'pendente')->count(),
            'pct_vermelho' => $pctVermelho,
            'pct_amarelo' => $pctAmarelo,
        ];
    }

    public static function dadosUnidade(int $clinicaId): array
    {
        $pctVermelho = (int) ConfigService::get('alerta_vermelho_percentual', '50');
        $pctAmarelo = (int) ConfigService::get('alerta_amarelo_percentual', '100');

        return [
            'estoque' => (int) DB::table('estoque')->where('clinica_id', $clinicaId)->sum('quantidade'),
            'itens' => DB::table('estoque')->where('clinica_id', $clinicaId)->count(),
            'saidas_hoje' => DB::table('saidas_medicamentos')->where('clinica_id', $clinicaId)->whereDate('data_saida', today())->count(),
            'entradas_hoje' => DB::table('entradas_medicamentos')->where('clinica_id', $clinicaId)->whereDate('data_entrada', today())->count(),
            'criticos' => self::contarAlertasClinica($clinicaId, $pctVermelho, 'critico'),
            'amarelos' => self::contarAlertasClinica($clinicaId, $pctAmarelo, $pctVermelho, 'amarelo'),
            'transf_pendentes' => DB::table('transferencias')->where('clinica_destino_id', $clinicaId)->where('status', 'pendente')->count(),
        ];
    }

    private static function contarAlertasClinica(int $clinicaId, int $pct1, ?int $pct2 = null, string $tipo = 'critico'): int
    {
        $query = DB::table('estoque as e')
            ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
            ->where('e.clinica_id', $clinicaId)
            ->where('m.estoque_minimo', '>', 0);

        if ($tipo === 'critico') {
            $query->whereRaw('e.quantidade <= (m.estoque_minimo * ? / 100)', [$pct1]);
        } else {
            $query->whereRaw('e.quantidade <= (m.estoque_minimo * ? / 100)', [$pct1])
                ->whereRaw('e.quantidade > (m.estoque_minimo * ? / 100)', [$pct2]);
        }

        return $query->count();
    }

    public static function cardsClinicas(): Collection
    {
        return DB::table('clinicas as c')
            ->where('c.ativo', 1)
            ->orderByDesc('c.eh_central')
            ->orderBy('c.nome')
            ->get()
            ->map(function ($clinica) {
                $estoque = (int) DB::table('estoque')->where('clinica_id', $clinica->id)->sum('quantidade');
                $criticos = self::contarAlertasClinica((int) $clinica->id, (int) ConfigService::get('alerta_vermelho_percentual', '50'));

                return (object) [
                    'id' => $clinica->id,
                    'nome' => $clinica->nome,
                    'eh_central' => $clinica->eh_central,
                    'estoque' => $estoque,
                    'criticos' => $criticos,
                    'saidas_hoje' => DB::table('saidas_medicamentos')->where('clinica_id', $clinica->id)->whereDate('data_saida', today())->count(),
                ];
            });
    }

    public static function graficoSaidasPorClinica(): array
    {
        $rows = DB::table('saidas_medicamentos as s')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->whereDate('s.data_saida', today())
            ->select('c.nome', DB::raw('SUM(s.quantidade) as total'))
            ->groupBy('c.nome')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $cores = ['#064e3b', '#0f766e', '#14b8a6', '#2dd4bf', '#5eead4', '#0891b2', '#6366f1', '#8b5cf6'];

        return $rows->values()->map(function ($r, $i) use ($cores) {
            return [
                'label' => $r->nome,
                'valor' => (float) $r->total,
                'cor' => $cores[$i % count($cores)],
            ];
        })->all();
    }

    public static function ultimasSaidas(?int $clinicaId = null, int $limit = 10): Collection
    {
        $q = DB::table('saidas_medicamentos as s')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->join('pacientes as p', 'p.id', '=', 's.paciente_id')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->select('s.*', 'm.nome as medicamento', 'm.dosagem', 'p.nome as paciente', 'c.nome as clinica')
            ->orderByDesc('s.data_saida')
            ->limit($limit);

        if ($clinicaId) {
            $q->where('s.clinica_id', $clinicaId);
        }

        return $q->get();
    }
}

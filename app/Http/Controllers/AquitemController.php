<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AquitemController extends Controller
{
    public function index(Request $request)
    {
        $termo = trim((string) $request->query('q', ''));
        $resultados = collect();
        $totalUnidades = 0;
        $clinicasUnicas = 0;

        if ($termo !== '') {
            $like = '%' . $termo . '%';
            $resultados = DB::table('estoque as e')
                ->join('clinicas as c', 'c.id', '=', 'e.clinica_id')
                ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
                ->where('e.quantidade', '>', 0)
                ->where('c.ativo', 1)
                ->where('m.ativo', 1)
                ->where(function ($q) use ($like) {
                    $q->where('m.nome', 'like', $like)
                        ->orWhere('m.principio_ativo', 'like', $like)
                        ->orWhere('m.codigo', 'like', $like)
                        ->orWhere('m.codigo_barras', 'like', $like);
                })
                ->select(
                    'c.id as clinica_id', 'c.nome as clinica_nome', 'c.endereco as clinica_endereco',
                    'c.bairro as clinica_bairro', 'c.telefone as clinica_telefone', 'c.eh_central',
                    'm.id as medicamento_id', 'm.nome as medicamento_nome', 'm.dosagem', 'm.unidade',
                    'm.principio_ativo', 'm.estoque_minimo', 'e.quantidade', 'e.atualizado_em'
                )
                ->orderBy('m.nome')
                ->orderByDesc('c.eh_central')
                ->orderBy('c.nome')
                ->limit(500)
                ->get();

            $totalUnidades = $resultados->sum('quantidade');
            $clinicasUnicas = $resultados->pluck('clinica_id')->unique()->count();
        }

        return view('aqui-tem.index', compact('termo', 'resultados', 'totalUnidades', 'clinicasUnicas'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstoqueController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim($request->query('q', ''));
        $clinicaFiltro = (int) $request->query('clinica_id', 0);

        $query = DB::table('estoque as e')
            ->join('clinicas as c', 'c.id', '=', 'e.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
            ->select(
                'e.*',
                'c.nome as clinica_nome',
                'm.nome as medicamento_nome',
                'm.dosagem',
                'm.unidade',
                'm.codigo',
                'm.codigo_barras',
                'm.estoque_minimo'
            );

        $ehAdmin = in_array(session('perfil'), ['superuser', 'admin', 'gestor'], true);
        if (!$ehAdmin && session('clinica_id')) {
            $query->where('e.clinica_id', session('clinica_id'));
        } elseif ($clinicaFiltro > 0) {
            $query->where('e.clinica_id', $clinicaFiltro);
        }

        if ($busca !== '') {
            $digitos = preg_replace('/\D/', '', $busca);
            $query->where(function ($q) use ($busca, $digitos) {
                $q->where('m.nome', 'like', '%' . $busca . '%')
                    ->orWhere('m.principio_ativo', 'like', '%' . $busca . '%')
                    ->orWhere('m.codigo', 'like', '%' . $busca . '%')
                    ->orWhere('m.codigo_barras', 'like', '%' . $busca . '%');
                if ($digitos !== '') {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(m.codigo, '.', ''), '-', ''), ' ', '') LIKE ?", ['%' . $digitos . '%']);
                }
            });
        }

        $itens = $query->orderBy('c.nome')->orderBy('m.nome')->limit(500)->get()->map(function ($item) {
            $item->classificacao = EstoqueService::classificar((int) $item->quantidade, (int) $item->estoque_minimo);
            [$item->badge_class, $item->badge_label] = EstoqueService::badge($item->classificacao);

            return $item;
        });

        $clinicas = $ehAdmin
            ? DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get(['id', 'nome'])
            : collect();

        return view('estoque.index', compact('itens', 'busca', 'clinicaFiltro', 'clinicas', 'ehAdmin'));
    }
}

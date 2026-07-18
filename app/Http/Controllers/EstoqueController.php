<?php

namespace App\Http\Controllers;

use App\Services\EstoqueService;
use Illuminate\Support\Facades\DB;

class EstoqueController extends Controller
{
    public function index()
    {
        $query = DB::table('estoque as e')
            ->join('clinicas as c', 'c.id', '=', 'e.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
            ->select('e.*', 'c.nome as clinica_nome', 'm.nome as medicamento_nome', 'm.dosagem', 'm.unidade', 'm.estoque_minimo');

        if (!in_array(session('perfil'), ['superuser', 'admin', 'gestor'], true) && session('clinica_id')) {
            $query->where('e.clinica_id', session('clinica_id'));
        }

        $itens = $query->orderBy('c.nome')->orderBy('m.nome')->get()->map(function ($item) {
            $item->classificacao = EstoqueService::classificar((int) $item->quantidade, (int) $item->estoque_minimo);
            [$item->badge_class, $item->badge_label] = EstoqueService::badge($item->classificacao);

            return $item;
        });

        return view('estoque.index', compact('itens'));
    }
}

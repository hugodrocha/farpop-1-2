<?php

namespace App\Http\Controllers;

use App\Services\LoteService;
use App\Services\ModuloService;
use App\Services\PerfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimentacaoController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'estoque')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        LoteService::inicializar();

        $clinicaId = PerfilService::ehAdmin($perfil)
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $q = DB::table('estoque_movimentacoes as mv')
            ->join('medicamentos as m', 'm.id', '=', 'mv.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 'mv.clinica_id')
            ->leftJoin('estoque_lotes as l', 'l.id', '=', 'mv.lote_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 'mv.usuario_id')
            ->select('mv.*', 'm.nome as medicamento', 'c.nome as clinica', 'l.lote', 'u.nome_completo as usuario')
            ->orderByDesc('mv.criado_em')
            ->limit(300);

        if ($clinicaId > 0) {
            $q->where('mv.clinica_id', $clinicaId);
        }

        $movimentacoes = $q->get();
        $tipos = config('farpop.movimentacoes');

        return view('movimentacoes.index', compact('movimentacoes', 'tipos', 'clinicaId'));
    }
}

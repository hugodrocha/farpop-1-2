<?php

namespace App\Http\Controllers;

use App\Services\LoteService;
use App\Services\ModuloService;
use App\Services\PerfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'estoque', 'lotes')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = PerfilService::ehAdmin($perfil)
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $medicamentoId = (int) $request->query('medicamento_id', 0);
        $lotes = LoteService::listarLotes($clinicaId ?: null, $medicamentoId ?: null, $request->query('todos') !== '1');

        $clinicas = PerfilService::ehAdmin($perfil)
            ? DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get(['id', 'nome'])
            : collect();

        return view('lotes.index', compact('lotes', 'clinicaId', 'medicamentoId', 'clinicas', 'perfil'));
    }

    public function etiqueta(Request $request, int $id)
    {
        LoteService::inicializar();
        $lote = DB::table('estoque_lotes as l')
            ->join('medicamentos as m', 'm.id', '=', 'l.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 'l.clinica_id')
            ->where('l.id', $id)
            ->select('l.*', 'm.nome as medicamento', 'm.codigo', 'c.nome as clinica')
            ->firstOrFail();

        $lote->validade_fmt = \App\Services\ValidadeService::formatar($lote->validade);

        return view('lotes.etiqueta', compact('lote'));
    }
}

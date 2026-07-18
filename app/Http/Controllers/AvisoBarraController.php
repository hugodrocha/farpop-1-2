<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvisoBarraController extends Controller
{
    public function index()
    {
        $avisos = DB::table('avisos_barra as a')
            ->join('usuarios as u', 'u.id', '=', 'a.criado_por_id')
            ->select('a.*', 'u.nome_completo as autor')
            ->orderByDesc('a.criado_em')
            ->limit(50)
            ->get();

        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();

        return view('avisos.index', compact('avisos', 'clinicas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mensagem' => 'required|max:2000',
            'prioridade' => 'required|in:info,aviso,alerta,urgente',
            'para_todas' => 'required|boolean',
            'clinicas' => 'nullable|array',
            'termina_em' => 'required|date',
        ]);

        $id = DB::table('avisos_barra')->insertGetId([
            'mensagem' => $data['mensagem'],
            'prioridade' => $data['prioridade'],
            'para_todas' => $data['para_todas'] ? 1 : 0,
            'inicia_em' => now(),
            'termina_em' => $data['termina_em'],
            'ativo' => 1,
            'criado_por_id' => session('usuario_id'),
            'criado_em' => now(),
        ]);

        if (!$data['para_todas'] && !empty($data['clinicas'])) {
            foreach ($data['clinicas'] as $clinicaId) {
                DB::table('aviso_clinicas')->insert([
                    'aviso_id' => $id,
                    'clinica_id' => (int) $clinicaId,
                ]);
            }
        }

        AuditoriaService::registrar('AVISO_CRIADO', 'aviso#' . $id, substr($data['mensagem'], 0, 120));

        return back()->with('success', 'Aviso publicado na barra.');
    }

    public function encerrar(string $id)
    {
        DB::table('avisos_barra')->where('id', $id)->update([
            'ativo' => 0,
            'encerrado_em' => now(),
        ]);

        AuditoriaService::registrar('AVISO_ENCERRADO', 'aviso#' . $id, 'Encerrado manualmente');

        return back()->with('success', 'Aviso encerrado.');
    }
}

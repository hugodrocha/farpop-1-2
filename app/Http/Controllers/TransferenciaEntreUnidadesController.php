<?php

namespace App\Http\Controllers;

use App\Services\EstoqueService;
use App\Services\Fase5SchemaService;
use App\Services\ModuloService;
use App\Services\PerfilService;
use App\Services\TransferenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransferenciaEntreUnidadesController extends Controller
{
    public function create(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'transferencias', 'entre_unidades')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        Fase5SchemaService::garantir();

        $centralId = EstoqueService::getCentralId();
        $clinicaUsuario = (int) $request->session()->get('clinica_id', 0);
        $origemFixa = !PerfilService::ehAdmin($perfil) && $clinicaUsuario > 0;

        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $clinicasOrigem = $origemFixa
            ? $clinicas->where('id', $clinicaUsuario)
            : $clinicas->filter(fn ($c) => !$centralId || (int) $c->id !== $centralId);

        return view('transferencias.entre-unidades.create', compact(
            'clinicas',
            'clinicasOrigem',
            'centralId',
            'origemFixa',
            'clinicaUsuario'
        ));
    }

    public function store(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'transferencias', 'entre_unidades')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $centralId = EstoqueService::getCentralId();
        $clinicaUsuario = (int) $request->session()->get('clinica_id', 0);
        $origemFixa = !PerfilService::ehAdmin($perfil) && $clinicaUsuario > 0;

        $data = $request->validate([
            'clinica_origem_id' => 'required|integer',
            'clinica_destino_id' => 'required|integer|different:clinica_origem_id',
            'tipo_transferencia' => 'required|in:entre_unidades,unidade_matriz',
            'observacao' => 'nullable|max:500',
            'itens_json' => 'required|string',
        ]);

        $origemId = $origemFixa ? $clinicaUsuario : (int) $data['clinica_origem_id'];

        if (!PerfilService::podeEnviarTransferenciaEntreUnidades($perfil, $clinicaUsuario, $origemId)) {
            return back()->withErrors(['error' => 'Sem permissão para enviar desta unidade.'])->withInput();
        }

        $itens = json_decode($data['itens_json'], true);
        if (!is_array($itens)) {
            return back()->withErrors(['error' => 'Lista de itens inválida.'])->withInput();
        }

        try {
            $id = TransferenciaService::finalizarEnvio(
                $origemId,
                (int) $data['clinica_destino_id'],
                (int) $request->session()->get('usuario_id'),
                $data['observacao'] ?? null,
                $itens,
                $data['tipo_transferencia'],
                $centralId
            );

            return redirect()->route('transferencias.nota', $id)->with('success', 'Transferência enviada com sucesso!');
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ChatSchemaService;
use App\Services\PerfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $usuarios = DB::table('usuarios as u')
            ->leftJoin('clinicas as c', 'c.id', '=', 'u.clinica_id')
            ->select('u.*', 'c.nome as clinica_nome')
            ->orderBy('u.nome_completo')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();

        return view('usuarios.create', compact('clinicas'));
    }

    public function store(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nome_completo' => 'required|max:180',
            'cpf' => 'nullable|max:20',
            'cargo' => 'nullable|max:120',
            'email' => 'nullable|email|max:180',
            'usuario' => 'required|max:100|unique:usuarios,usuario',
            'senha' => 'required|min:4',
            'perfil' => 'required',
            'clinica_id' => 'nullable|integer',
            'bloqueado' => 'boolean',
        ]);

        ChatSchemaService::garantir();

        DB::table('usuarios')->insert([
            ...$data,
            'senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
            'chat_habilitado' => 1,
            'tentativas_erradas' => 0,
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function edit(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $usuario = DB::table('usuarios')->where('id', $id)->firstOrFail();
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();

        return view('usuarios.edit', compact('usuario', 'clinicas'));
    }

    public function update(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nome_completo' => 'required|max:180',
            'cpf' => 'nullable|max:20',
            'cargo' => 'nullable|max:120',
            'email' => 'nullable|email|max:180',
            'usuario' => 'required|max:100|unique:usuarios,usuario,' . $id,
            'senha' => 'nullable|min:4',
            'perfil' => 'required',
            'clinica_id' => 'nullable|integer',
            'bloqueado' => 'boolean',
        ]);

        $payload = [
            'nome_completo' => $data['nome_completo'],
            'cpf' => $data['cpf'],
            'cargo' => $data['cargo'],
            'email' => $data['email'],
            'usuario' => $data['usuario'],
            'perfil' => $data['perfil'],
            'clinica_id' => $data['clinica_id'],
            'bloqueado' => $data['bloqueado'] ?? 0,
            'atualizado_em' => now(),
        ];

        if (!empty($data['senha'])) {
            $payload['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        $perfilLogado = (string) $request->session()->get('perfil', '');
        if (PerfilService::podeConfigurarChatUsuario($perfilLogado)) {
            ChatSchemaService::garantir();
            $payload['chat_habilitado'] = $request->boolean('chat_habilitado') ? 1 : 0;
        }

        DB::table('usuarios')->where('id', $id)->update($payload);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        DB::table('usuarios')->where('id', $id)->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuário removido com sucesso.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrocarSenhaController extends Controller
{
    public function index()
    {
        return view('trocar-senha.index');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'senha_atual' => 'required',
            'nova_senha' => 'required|min:6|max:12|confirmed',
        ], [
            'nova_senha.confirmed' => 'A confirmação não coincide com a nova senha.',
        ]);

        $user = DB::table('usuarios')->where('id', session('usuario_id'))->first();

        if (!$user || !password_verify($data['senha_atual'], $user->senha)) {
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }

        DB::table('usuarios')->where('id', $user->id)->update([
            'senha' => password_hash($data['nova_senha'], PASSWORD_DEFAULT),
        ]);

        AuditoriaService::registrar('TROCA_SENHA', session('usuario'), 'Usuário trocou a própria senha.');

        return back()->with('success', 'Senha alterada com sucesso.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'senha' => 'required',
        ], [
            'usuario.required' => 'Informe o usuário.',
            'senha.required' => 'Informe a senha.',
        ]);

        $usuario = trim($request->input('usuario'));
        $senha = trim($request->input('senha'));

        $user = DB::table('usuarios')->where('usuario', $usuario)->first();

        if (!$user) {
            return back()->withErrors(['usuario' => 'Usuário não encontrado.'])->withInput();
        }

        if ((int)($user->bloqueado ?? 0) === 1) {
            return back()->withErrors(['usuario' => 'Usuário bloqueado. Procure um administrador.'])->withInput();
        }

        if (!password_verify($senha, $user->senha)) {
            $tentativas = (int)($user->tentativas_erradas ?? 0) + 1;
            $bloqueado = $tentativas >= 5 ? 1 : 0;
            DB::table('usuarios')->where('id', $user->id)->update([
                'tentativas_erradas' => $tentativas,
                'bloqueado' => $bloqueado,
            ]);

            if ($bloqueado) {
                return back()->withErrors(['usuario' => 'Senha incorreta. Usuário bloqueado por excesso de tentativas.'])->withInput();
            }

            return back()->withErrors(['usuario' => 'Senha incorreta. Tentativa ' . $tentativas . ' de 5.'])->withInput();
        }

        DB::table('usuarios')->where('id', $user->id)->update(['tentativas_erradas' => 0]);

        $request->session()->put('usuario_id', $user->id);
        $request->session()->put('usuario', $user->usuario);
        $request->session()->put('nome_completo', $user->nome_completo);
        $request->session()->put('perfil', $user->perfil);
        $request->session()->put('clinica_id', $user->clinica_id);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();

        return redirect()->route('login');
    }
}

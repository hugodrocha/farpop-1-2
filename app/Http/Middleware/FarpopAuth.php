<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Services\AvisoBarraService;
use App\Services\ChatService;
use App\Services\ConfigService;
use Symfony\Component\HttpFoundation\Response;

class FarpopAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        if (!$request->session()->has('clinica_nome') && $request->session()->has('clinica_id')) {
            $nome = DB::table('clinicas')->where('id', $request->session()->get('clinica_id'))->value('nome');
            if ($nome) {
                $request->session()->put('clinica_nome', $nome);
            }
        }

        $clinicaId = $request->session()->get('clinica_id');
        View::share('farpopAvisos', AvisoBarraService::buscarAvisosBarraAtivos($clinicaId ? (int) $clinicaId : null));
        View::share('farpopChatNaoLidas', ConfigService::chatHabilitado()
            ? ChatService::chatNaoLidasParaUsuario((int) $request->session()->get('usuario_id'))
            : 0);

        return $next($request);
    }
}

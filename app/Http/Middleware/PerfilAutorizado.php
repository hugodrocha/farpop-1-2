<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerfilAutorizado
{
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        $perfil = $request->session()->get('perfil', '');

        if (!empty($perfis) && !in_array($perfil, $perfis, true)) {
            $rota = $perfil === 'consulta' ? 'aqui-tem.index' : 'dashboard';
            return redirect()->route($rota)->withErrors(['error' => 'Acesso não permitido para seu perfil.']);
        }

        return $next($request);
    }
}

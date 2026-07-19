<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmTransitoController extends Controller
{
    public function index(Request $request)
    {
        $perfil = session('perfil', '');
        $clinicaId = (int) session('clinica_id', 0);
        $ehAdmin = in_array($perfil, ['superuser', 'admin', 'gestor'], true);

        $busca = trim($request->query('q', ''));
        $destinoId = (int) $request->query('destino_id', 0);
        $data = trim($request->query('data', ''));

        $query = DB::table('transferencias as t')
            ->join('clinicas as co', 'co.id', '=', 't.clinica_origem_id')
            ->join('clinicas as cd', 'cd.id', '=', 't.clinica_destino_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 't.usuario_envio_id')
            ->where('t.status', 'pendente')
            ->select('t.*', 'co.nome as clinica_origem', 'cd.nome as clinica_destino', 'u.nome_completo as usuario_nome');

        if (!$ehAdmin && $clinicaId > 0) {
            $query->where(function ($q) use ($clinicaId) {
                $q->where('t.clinica_destino_id', $clinicaId)->orWhere('t.clinica_origem_id', $clinicaId);
            });
        }

        if ($busca !== '') {
            $query->where(function ($q) use ($busca) {
                $q->where('t.codigo_nota', 'like', '%' . $busca . '%');
                if (ctype_digit($busca)) {
                    $q->orWhere('t.id', (int) $busca);
                }
            });
        }

        if ($destinoId > 0) {
            $query->where('t.clinica_destino_id', $destinoId);
        }

        if ($data !== '') {
            $query->whereDate('t.enviado_em', $data);
        }

        $transferencias = $query->orderByDesc('t.enviado_em')->get();

        foreach ($transferencias as $t) {
            $t->itens_count = DB::table('transferencia_itens')->where('transferencia_id', $t->id)->count();
        }

        $clinicas = $ehAdmin
            ? DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get(['id', 'nome'])
            : collect();

        return view('transferencias.transito', compact('transferencias', 'busca', 'destinoId', 'data', 'clinicas', 'ehAdmin'));
    }
}

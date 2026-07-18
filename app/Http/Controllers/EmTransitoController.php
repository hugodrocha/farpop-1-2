<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class EmTransitoController extends Controller
{
    public function index()
    {
        $perfil = session('perfil', '');
        $clinicaId = (int) session('clinica_id', 0);
        $ehAdmin = in_array($perfil, ['superuser', 'admin', 'gestor'], true);

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

        $transferencias = $query->orderByDesc('t.enviado_em')->get();

        foreach ($transferencias as $t) {
            $t->itens_count = DB::table('transferencia_itens')->where('transferencia_id', $t->id)->count();
        }

        return view('transferencias.transito', compact('transferencias'));
    }
}

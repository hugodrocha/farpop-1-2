<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditoriaChatController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('chat_mensagens')) {
            return view('auditoria.chat', ['mensagens' => collect()]);
        }

        $mensagens = DB::table('chat_mensagens as m')
            ->join('usuarios as r', 'r.id', '=', 'm.remetente_id')
            ->leftJoin('usuarios as d', 'd.id', '=', 'm.destinatario_id')
            ->select('m.*', 'r.nome_completo as remetente', 'd.nome_completo as destinatario')
            ->orderByDesc('m.enviado_em')
            ->limit(300)
            ->get();

        return view('auditoria.chat', compact('mensagens'));
    }
}

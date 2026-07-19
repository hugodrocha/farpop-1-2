<?php

namespace App\Http\Controllers;

use App\Services\ChatSchemaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditoriaChatController extends Controller
{
    public function index()
    {
        ChatSchemaService::garantir();

        if (!Schema::hasTable('chat_mensagens')) {
            return view('auditoria.chat', ['mensagens' => collect()]);
        }

        $mensagens = DB::table('chat_mensagens as m')
            ->join('usuarios as r', 'r.id', '=', 'm.remetente_id')
            ->leftJoin('chat_conversas as c', 'c.id', '=', 'm.conversa_id')
            ->leftJoin('usuarios as d', 'd.id', '=', 'm.destinatario_id')
            ->select(
                'm.*',
                'r.nome_completo as remetente',
                'd.nome_completo as destinatario',
                'c.tipo as conversa_tipo',
                'c.titulo as conversa_titulo',
                'c.id as conversa_id'
            )
            ->orderByDesc('m.enviado_em')
            ->limit(300)
            ->get();

        $conversaIds = $mensagens->pluck('conversa_id')->filter()->unique()->values();
        $membrosPorConversa = [];

        if ($conversaIds->isNotEmpty() && Schema::hasTable('chat_conversa_membros')) {
            $membros = DB::table('chat_conversa_membros as cm')
                ->join('usuarios as u', 'u.id', '=', 'cm.usuario_id')
                ->whereIn('cm.conversa_id', $conversaIds)
                ->orderBy('u.nome_completo')
                ->get(['cm.conversa_id', 'u.nome_completo']);

            foreach ($membros as $row) {
                $membrosPorConversa[(int) $row->conversa_id][] = (string) $row->nome_completo;
            }
        }

        return view('auditoria.chat', compact('mensagens', 'membrosPorConversa'));
    }
}

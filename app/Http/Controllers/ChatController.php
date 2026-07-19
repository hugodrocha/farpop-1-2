<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use App\Services\ConfigService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        if (!ChatService::usuarioPodeUsar((int) session('usuario_id'))) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Chat indisponível para sua conta.']);
        }

        return view('chat.index');
    }

    /**
     * API compatível com o TelReg-Chat (acao=ping|contatos|mensagens|marcar_lido|enviar|criar_grupo).
     */
    public function api(Request $request)
    {
        $meuId = (int) session('usuario_id');
        if ($meuId <= 0) {
            return response()->json(['ok' => false, 'erro' => 'Sessão expirada.'], 401);
        }

        if (!ChatService::usuarioPodeUsar($meuId)) {
            return response()->json(['ok' => false, 'erro' => 'Conta inativa para o chat.'], 403);
        }

        $acao = trim($request->input('acao', ''));

        if ($acao === 'ping') {
            ChatService::ping($meuId);

            return response()->json(['ok' => true]);
        }

        if ($acao === 'contatos') {
            $data = ChatService::contatos($meuId);

            return response()->json(['ok' => true, ...$data]);
        }

        if ($acao === 'mensagens') {
            $conversaId = (int) $request->input('conversa_id', 0);
            $outroId = (int) $request->input('outro_id', 0);
            $sinceId = (int) $request->input('since_id', 0);

            if ($conversaId <= 0 && $outroId > 0) {
                $conversaId = ChatService::conversaIdPorOutro($meuId, $outroId);
            }

            if ($conversaId <= 0) {
                return response()->json(['ok' => false, 'erro' => 'Conversa inválida.']);
            }

            return response()->json([
                'ok' => true,
                'conversa_id' => $conversaId,
                'mensagens' => ChatService::mensagens($meuId, $conversaId, $sinceId),
            ]);
        }

        if ($acao === 'marcar_lido') {
            $conversaId = (int) $request->input('conversa_id', 0);
            $outroId = (int) $request->input('outro_id', 0);

            if ($conversaId <= 0 && $outroId > 0) {
                $conversaId = ChatService::conversaIdPorOutro($meuId, $outroId);
            }

            if ($conversaId <= 0) {
                return response()->json(['ok' => false, 'erro' => 'Conversa inválida.']);
            }

            $ultimo = ChatService::marcarLido($meuId, $conversaId);

            return response()->json(['ok' => true, 'ultimo_id_lido' => $ultimo]);
        }

        if ($acao === 'enviar') {
            $texto = trim((string) $request->input('texto', $request->input('mensagem', '')));
            $conversaId = (int) $request->input('conversa_id', 0);
            $outroId = (int) $request->input('outro_id', 0);

            if ($conversaId > 0) {
                $result = ChatService::enviar($meuId, $conversaId, $texto);
            } elseif ($outroId > 0) {
                $result = ChatService::enviarParaUsuario($meuId, $outroId, $texto);
            } else {
                return response()->json(['ok' => false, 'erro' => 'Destino inválido.']);
            }

            return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
        }

        if ($acao === 'criar_grupo') {
            $titulo = trim((string) $request->input('titulo', ''));
            $membros = $request->input('membros', []);
            if (!is_array($membros)) {
                $membros = [];
            }

            $result = ChatService::criarGrupo($meuId, $titulo, $membros);

            return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
        }

        if ($acao === 'resumo') {
            return response()->json([
                'ok' => true,
                'total_nao_lidas' => ChatService::chatNaoLidasParaUsuario($meuId),
                'habilitado' => ConfigService::chatHabilitado(),
            ]);
        }

        return response()->json(['ok' => false, 'erro' => 'Ação inválida.'], 400);
    }

    /** Rotas legadas usadas pela página /chat */
    public function mensagens(Request $request)
    {
        $meuId = (int) session('usuario_id');
        $alvo = $request->query('alvo', '');
        $conversaId = (int) $request->query('conversa_id', 0);

        if ($conversaId <= 0 && is_numeric($alvo)) {
            $conversaId = ChatService::conversaIdPorOutro($meuId, (int) $alvo);
        }

        if ($conversaId <= 0) {
            return response()->json(['ok' => true, 'mensagens' => []]);
        }

        ChatService::marcarLido($meuId, $conversaId);

        return response()->json([
            'ok' => true,
            'mensagens' => ChatService::mensagens($meuId, $conversaId, 0),
            'conversa_id' => $conversaId,
        ]);
    }

    public function usuarios()
    {
        $data = ChatService::contatos((int) session('usuario_id'));

        return response()->json([
            'usuarios' => $data['contatos'],
            'grupos' => $data['grupos'],
            'geral_nao_lidas' => 0,
        ]);
    }

    public function enviar(Request $request)
    {
        $data = $request->validate([
            'mensagem' => 'required|max:4000',
            'alvo' => 'nullable',
            'conversa_id' => 'nullable|integer',
        ]);

        $meuId = (int) session('usuario_id');
        $texto = trim($data['mensagem']);

        if (!empty($data['conversa_id'])) {
            $result = ChatService::enviar($meuId, (int) $data['conversa_id'], $texto);
        } elseif (!empty($data['alvo']) && is_numeric($data['alvo'])) {
            $result = ChatService::enviarParaUsuario($meuId, (int) $data['alvo'], $texto);
        } else {
            return response()->json(['ok' => false, 'erro' => 'Destino inválido.']);
        }

        return response()->json($result);
    }
}

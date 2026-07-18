<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use App\Services\ConfigService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        if (!ConfigService::chatHabilitado()) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Chat desabilitado pelo administrador.']);
        }

        return view('chat.index');
    }

    public function mensagens(Request $request)
    {
        $alvo = $request->query('alvo', 'geral');

        return response()->json([
            'ok' => true,
            'mensagens' => ChatService::listar((int) session('usuario_id'), $alvo),
        ]);
    }

    public function usuarios()
    {
        return response()->json(ChatService::listarUsuarios((int) session('usuario_id')));
    }

    public function enviar(Request $request)
    {
        $data = $request->validate([
            'alvo' => 'required',
            'mensagem' => 'required|max:2000',
        ]);

        $result = ChatService::enviar(
            (int) session('usuario_id'),
            $data['alvo'],
            trim($data['mensagem']),
            session('perfil') === 'superuser'
        );

        return response()->json($result);
    }
}

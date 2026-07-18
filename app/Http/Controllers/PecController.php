<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use App\Services\PecService;
use Illuminate\Http\Request;

class PecController extends Controller
{
    public function buscarCidadao(Request $request)
    {
        $cpf = preg_replace('/\D/', '', $request->query('cpf', ''));

        if (strlen($cpf) !== 11) {
            return response()->json(['ok' => false, 'erro' => 'CPF inválido.']);
        }

        $status = PecService::status();
        if (!$status['ok']) {
            return response()->json(['ok' => false, 'erro' => $status['motivo'] ?? 'PEC indisponível.']);
        }

        $cidadao = PecService::buscarCidadaoPorCpf($cpf);
        if (!$cidadao) {
            return response()->json(['ok' => false, 'erro' => 'Cidadão não encontrado no PEC.']);
        }

        return response()->json(['ok' => true, 'cidadao' => $cidadao]);
    }
}

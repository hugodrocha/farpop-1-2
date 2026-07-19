<?php

namespace App\Http\Controllers;

use App\Services\MedicamentoBuscaService;
use App\Services\PerfilService;
use Illuminate\Http\Request;

class MedicamentoBuscaController extends Controller
{
    public function buscar(Request $request)
    {
        $termo = trim($request->query('q', $request->query('codigo', '')));
        $clinicaId = $this->clinicaConsulta($request);

        if ($termo === '') {
            return response()->json(['ok' => false, 'erro' => 'Informe código, código de barras ou nome.']);
        }

        $med = MedicamentoBuscaService::buscarPorCodigoOuBarras($termo);
        if (!$med && mb_strlen($termo) >= 2) {
            $med = MedicamentoBuscaService::autocomplete($termo, 1)->first();
        }

        if (!$med) {
            return response()->json(['ok' => false, 'erro' => 'Medicamento não encontrado.']);
        }

        return response()->json([
            'ok' => true,
            'medicamento' => MedicamentoBuscaService::payload($med, $clinicaId),
        ]);
    }

    public function autocomplete(Request $request)
    {
        $termo = trim($request->query('q', ''));
        $clinicaId = $this->clinicaConsulta($request);

        $itens = MedicamentoBuscaService::autocomplete($termo)->map(
            fn ($med) => MedicamentoBuscaService::payload($med, $clinicaId)
        );

        return response()->json(['ok' => true, 'itens' => $itens]);
    }

    private function clinicaConsulta(Request $request): ?int
    {
        if (PerfilService::ehAdmin(session('perfil', ''))) {
            $id = (int) $request->query('clinica_id', 0);

            return $id > 0 ? $id : null;
        }

        $id = (int) session('clinica_id', 0);

        return $id > 0 ? $id : null;
    }
}

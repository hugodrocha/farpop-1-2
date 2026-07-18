<?php

namespace App\Http\Controllers;

use App\Services\CodigoBarrasService;
use Illuminate\Support\Facades\DB;

class NotaTransferenciaController extends Controller
{
    public function show(string $id)
    {
        $t = DB::table('transferencias as t')
            ->join('clinicas as co', 'co.id', '=', 't.clinica_origem_id')
            ->join('clinicas as cd', 'cd.id', '=', 't.clinica_destino_id')
            ->leftJoin('usuarios as ue', 'ue.id', '=', 't.usuario_envio_id')
            ->where('t.id', $id)
            ->select('t.*', 'co.nome as clinica_origem', 'cd.nome as clinica_destino', 'ue.nome_completo as enviado_por')
            ->firstOrFail();

        $itens = DB::table('transferencia_itens as ti')
            ->join('medicamentos as m', 'm.id', '=', 'ti.medicamento_id')
            ->where('ti.transferencia_id', $id)
            ->select('ti.*', 'm.nome', 'm.dosagem', 'm.unidade')
            ->orderBy('m.nome')
            ->get();

        $codigoNota = $t->codigo_nota ?? '';
        $barcodeSvg = $codigoNota ? CodigoBarrasService::code128Svg($codigoNota, 2, 70) : '';

        return view('transferencias.nota', compact('t', 'itens', 'barcodeSvg', 'codigoNota'));
    }
}

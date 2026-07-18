<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\EstoqueService;
use App\Services\GraficoService;
use App\Services\PerfilService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $perfil = session('perfil', '');
        $clinicaId = (int) session('clinica_id', 0);
        $visaoGlobal = DashboardService::visaoGlobal($perfil, $clinicaId);

        if ($visaoGlobal) {
            $totais = DashboardService::dadosGlobais();
            $cardsClinicas = DashboardService::cardsClinicas();
            $graficoSaidas = GraficoService::graficoPizzaSvg(
                DashboardService::graficoSaidasPorClinica(),
                ['titulo' => 'Dispensações hoje por unidade', 'largura' => 420, 'altura' => 280]
            );
            $ultimasSaidas = DashboardService::ultimasSaidas(null, 8);
        } else {
            $totais = DashboardService::dadosUnidade($clinicaId);
            $totais['clinica_nome'] = session('clinica_nome');
            $cardsClinicas = collect();
            $graficoSaidas = '';
            $ultimasSaidas = DashboardService::ultimasSaidas($clinicaId, 8);
        }

        return view('dashboard.index', compact(
            'totais',
            'visaoGlobal',
            'cardsClinicas',
            'graficoSaidas',
            'ultimasSaidas',
            'perfil'
        ));
    }
}

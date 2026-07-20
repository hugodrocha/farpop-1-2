<?php

namespace App\Http\Controllers;

use App\Services\AlertaVencimentoService;
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
            $alertasVencimento = AlertaVencimentoService::contar(null);
        } else {
            $totais = DashboardService::dadosUnidade($clinicaId);
            $totais['clinica_nome'] = session('clinica_nome');
            $cardsClinicas = collect();
            $graficoSaidas = '';
            $ultimasSaidas = DashboardService::ultimasSaidas($clinicaId, 8);
            $alertasVencimento = AlertaVencimentoService::contar($clinicaId);
        }

        return view('dashboard.index', compact(
            'totais',
            'visaoGlobal',
            'cardsClinicas',
            'graficoSaidas',
            'ultimasSaidas',
            'perfil',
            'alertasVencimento'
        ));
    }

    public function clinica(Request $request, int $id)
    {
        $perfil = session('perfil', '');
        $clinicaUsuarioId = (int) session('clinica_id', 0);

        if (!DashboardService::podeVerClinica($perfil, $clinicaUsuarioId, $id)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão para ver esta unidade.']);
        }

        $totais = DashboardService::dadosClinica($id);
        if ($totais === []) {
            abort(404);
        }

        $graficoEstoque = GraficoService::graficoBarrasSvg(
            DashboardService::topMedicamentosEstoque($id),
            ['titulo' => 'Maior estoque por medicamento', 'largura' => 520, 'altura' => 280]
        );

        $graficoDispensados = GraficoService::graficoBarrasSvg(
            DashboardService::topMedicamentosDispensados($id),
            ['titulo' => 'Mais dispensados (30 dias)', 'largura' => 520, 'altura' => 280]
        );

        $mov = DashboardService::movimentacaoUltimosDias($id);
        $graficoMovimentacao = GraficoService::graficoBarrasAgrupadasSvg(
            $mov['labels'],
            [
                ['label' => 'Entradas', 'valores' => $mov['entradas'], 'cor' => '#059669'],
                ['label' => 'Dispensações', 'valores' => $mov['saidas'], 'cor' => '#dc2626'],
            ],
            ['titulo' => 'Entradas x dispensações (7 dias)', 'largura' => 520, 'altura' => 260]
        );

        $ultimasSaidas = DashboardService::ultimasSaidas($id, 10);

        return view('dashboard.clinica', compact(
            'totais',
            'graficoEstoque',
            'graficoDispensados',
            'graficoMovimentacao',
            'ultimasSaidas'
        ));
    }
}

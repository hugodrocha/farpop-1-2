<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $totais = [
            'clinicas' => DB::table('clinicas')->where('ativo', 1)->count(),
            'medicamentos' => DB::table('medicamentos')->where('ativo', 1)->count(),
            'estoque' => (int) DB::table('estoque')->sum('quantidade'),
            'usuarios' => DB::table('usuarios')->count(),
        ];

        return view('dashboard.index', compact('totais'));
    }
}

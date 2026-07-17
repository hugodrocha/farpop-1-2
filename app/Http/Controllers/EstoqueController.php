<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstoqueController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $itens = DB::table('estoque as e')
            ->join('clinicas as c', 'c.id', '=', 'e.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
            ->select('e.*', 'c.nome as clinica_nome', 'm.nome as medicamento_nome', 'm.dosagem', 'm.unidade')
            ->orderBy('c.nome')
            ->orderBy('m.nome')
            ->get();

        return view('estoque.index', compact('itens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

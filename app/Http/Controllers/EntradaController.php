<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use App\Services\EstoqueService;
use App\Services\Fase4SchemaService;
use App\Services\LoteService;
use App\Services\PerfilService;
use App\Services\ValidadeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntradaController extends Controller
{
    public function index()
    {
        $query = DB::table('entradas_medicamentos as en')
            ->join('clinicas as c', 'c.id', '=', 'en.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 'en.medicamento_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 'en.usuario_id')
            ->select('en.*', 'c.nome as clinica_nome', 'm.nome as medicamento_nome', 'm.dosagem', 'u.nome_completo as usuario_nome')
            ->orderByDesc('en.data_entrada')
            ->limit(100);

        if (!PerfilService::ehAdmin(session('perfil', '')) && session('clinica_id')) {
            $query->where('en.clinica_id', session('clinica_id'));
        }

        $entradas = $query->get();

        return view('entradas.index', compact('entradas'));
    }

    public function create()
    {
        $perfil = session('perfil', '');
        $clinicaFixa = !PerfilService::ehAdmin($perfil) && session('clinica_id');
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        Fase4SchemaService::garantir();
        $fornecedores = DB::table('fornecedores')->where('ativo', 1)->orderBy('nome')->get();
        $fabricantes = DB::table('fabricantes')->where('ativo', 1)->orderBy('nome')->get();

        return view('entradas.create', compact('clinicas', 'clinicaFixa', 'fornecedores', 'fabricantes'));
    }

    public function store(Request $request)
    {
        $perfil = session('perfil', '');
        $clinicaFixa = !PerfilService::ehAdmin($perfil) && session('clinica_id');

        $data = $request->validate([
            'clinica_id' => 'required|integer',
            'nota_fiscal' => 'nullable|max:80',
            'origem' => 'nullable|max:160',
            'observacao' => 'nullable|max:255',
            'med_id' => 'required|array|min:1',
            'med_id.*' => 'integer',
            'med_qtd' => 'required|array',
            'med_qtd.*' => 'integer|min:1',
            'med_lote' => 'nullable|array',
            'med_validade' => 'nullable|array',
            'med_fabricante' => 'nullable|array',
            'med_obs' => 'nullable|array',
            'tipo_entrada' => 'nullable|in:entrada_compra,entrada_doacao,devolucao',
            'fornecedor_id' => 'nullable|integer',
        ]);

        $clinicaId = $clinicaFixa ? (int) session('clinica_id') : (int) $data['clinica_id'];
        $itens = [];
        $totalQtd = 0;

        foreach ($data['med_id'] as $i => $medId) {
            $medId = (int) $medId;
            $qtd = (int) ($data['med_qtd'][$i] ?? 0);
            if ($medId <= 0 || $qtd <= 0) {
                continue;
            }

            $validade = trim($data['med_validade'][$i] ?? '');
            if ($validade !== '' && !ValidadeService::valida($validade)) {
                return back()->withErrors(['error' => 'Validade inválida no item #' . ($i + 1) . '. Use MM/AAAA ou DD/MM/AAAA.'])->withInput();
            }

            $itens[] = [
                'medicamento_id' => $medId,
                'quantidade' => $qtd,
                'lote' => trim($data['med_lote'][$i] ?? '') ?: null,
                'fabricante' => trim($data['med_fabricante'][$i] ?? '') ?: null,
                'validade' => ValidadeService::normalizar($validade ?: null),
                'observacao' => trim($data['med_obs'][$i] ?? '') ?: null,
            ];
            $totalQtd += $qtd;
        }

        if (empty($itens)) {
            return back()->withErrors(['error' => 'Adicione ao menos um medicamento.'])->withInput();
        }

        DB::transaction(function () use ($clinicaId, $data, $itens, $totalQtd) {
            $uid = session('usuario_id');
            Fase4SchemaService::garantir();

            foreach ($itens as $item) {
                $tipoEntrada = trim($data['tipo_entrada'] ?? 'entrada_compra') ?: 'entrada_compra';

                $entradaId = DB::table('entradas_medicamentos')->insertGetId([
                    'clinica_id' => $clinicaId,
                    'medicamento_id' => $item['medicamento_id'],
                    'quantidade' => $item['quantidade'],
                    'lote' => $item['lote'],
                    'fabricante' => $item['fabricante'] ?? null,
                    'validade' => $item['validade'],
                    'tipo_entrada' => $tipoEntrada,
                    'nota_fiscal' => $data['nota_fiscal'] ?? null,
                    'origem' => $data['origem'] ?? null,
                    'fornecedor_id' => !empty($data['fornecedor_id']) ? (int) $data['fornecedor_id'] : null,
                    'observacao' => $item['observacao'] ?? $data['observacao'] ?? null,
                    'usuario_id' => $uid,
                    'data_entrada' => now(),
                ]);

                LoteService::adicionarLote(
                    $clinicaId,
                    $item['medicamento_id'],
                    $item['quantidade'],
                    $tipoEntrada,
                    $item['lote'],
                    $item['validade'],
                    $item['fabricante'] ?? null,
                    null,
                    $entradaId,
                    'entrada',
                    $entradaId,
                    $uid ? (int) $uid : null,
                    $item['observacao'] ?? null
                );
            }

            AuditoriaService::registrar(
                'ENTRADA_MEDICAMENTO',
                'clinica:' . $clinicaId,
                count($itens) . ' item(ns), total ' . $totalQtd . ' un.'
            );
        });

        $msg = count($itens) === 1
            ? 'Entrada registrada com sucesso.'
            : count($itens) . ' entradas registradas com sucesso.';

        return redirect()->route('entradas.index')->with('success', $msg);
    }

    public function edit(string $id)
    {
        $entrada = DB::table('entradas_medicamentos')->where('id', $id)->firstOrFail();
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();

        return view('entradas.edit', compact('entrada', 'clinicas', 'medicamentos'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'clinica_id' => 'required|integer',
            'medicamento_id' => 'required|integer',
            'quantidade' => 'required|integer|min:1',
            'lote' => 'nullable|max:80',
            'validade' => ['nullable', 'max:20', function ($attribute, $value, $fail) {
                if (!ValidadeService::valida($value)) {
                    $fail('Validade inválida. Use MM/AAAA ou DD/MM/AAAA.');
                }
            }],
            'nota_fiscal' => 'nullable|max:80',
            'origem' => 'nullable|max:160',
            'observacao' => 'nullable|max:255',
        ]);

        DB::table('entradas_medicamentos')->where('id', $id)->update([
            'clinica_id' => $data['clinica_id'],
            'medicamento_id' => $data['medicamento_id'],
            'quantidade' => $data['quantidade'],
            'lote' => $data['lote'] ?? null,
            'validade' => ValidadeService::normalizar($data['validade'] ?? null),
            'nota_fiscal' => $data['nota_fiscal'] ?? null,
            'origem' => $data['origem'] ?? null,
            'observacao' => $data['observacao'] ?? null,
        ]);

        return redirect()->route('entradas.index')->with('success', 'Entrada atualizada.');
    }

    public function destroy(string $id)
    {
        DB::table('entradas_medicamentos')->where('id', $id)->delete();

        return redirect()->route('entradas.index')->with('success', 'Entrada removida.');
    }
}

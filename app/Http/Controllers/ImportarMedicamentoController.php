<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportarMedicamentoController extends Controller
{
    public function index()
    {
        return view('medicamentos.importar');
    }

    public function store(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt|max:10240']);

        $path = $request->file('arquivo')->getRealPath();
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return back()->withErrors(['error' => 'Não foi possível ler o arquivo.']);
        }

        $primeira = fgets($handle);
        if (!$primeira) {
            fclose($handle);

            return back()->withErrors(['error' => 'Arquivo vazio.']);
        }

        $delim = $this->detectarDelimitador($primeira);
        rewind($handle);
        $cab = fgetcsv($handle, 0, $delim);
        if (!$cab) {
            fclose($handle);

            return back()->withErrors(['error' => 'Cabeçalho inválido.']);
        }

        $mapa = [];
        foreach ($cab as $i => $col) {
            $mapa[$this->normalizarCabecalho((string) $col)] = $i;
        }

        $importados = 0;
        $atualizados = 0;

        while (($linha = fgetcsv($handle, 0, $delim)) !== false) {
            $nome = $this->valorColuna($linha, $mapa, ['nome', 'medicamento', 'produto']);
            if ($nome === '') {
                continue;
            }

            $payload = [
                'nome' => $nome,
                'principio_ativo' => $this->valorColuna($linha, $mapa, ['principio_ativo', 'principio', 'substancia']),
                'forma_farmaceutica' => $this->valorColuna($linha, $mapa, ['forma_farmaceutica', 'forma']),
                'dosagem' => $this->valorColuna($linha, $mapa, ['dosagem', 'concentracao']),
                'unidade' => $this->valorColuna($linha, $mapa, ['unidade', 'apresentacao']) ?: 'UNIDADE',
                'codigo' => $this->valorColuna($linha, $mapa, ['codigo', 'cod']),
                'codigo_barras' => $this->valorColuna($linha, $mapa, ['codigo_barras', 'ean', 'barcode']),
                'ativo' => 1,
            ];

            $existente = DB::table('medicamentos')
                ->where(function ($q) use ($payload) {
                    $q->where('nome', $payload['nome']);
                    if ($payload['codigo'] !== '') {
                        $q->orWhere('codigo', $payload['codigo']);
                    }
                })
                ->first();

            if ($existente) {
                DB::table('medicamentos')->where('id', $existente->id)->update($payload);
                $atualizados++;
            } else {
                DB::table('medicamentos')->insert($payload);
                $importados++;
            }
        }

        fclose($handle);

        AuditoriaService::registrar('IMPORT_REMUME', 'medicamentos', "Importados: {$importados}, atualizados: {$atualizados}");

        return back()->with('success', "Importação concluída: {$importados} novo(s), {$atualizados} atualizado(s).");
    }

    private function detectarDelimitador(string $linha): string
    {
        $cont = [',' => substr_count($linha, ','), ';' => substr_count($linha, ';'), "\t" => substr_count($linha, "\t")];
        arsort($cont);

        return array_key_first($cont);
    }

    private function normalizarCabecalho(string $s): string
    {
        $s = mb_strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $s)), 'UTF-8');
        $s = preg_replace('/[^a-z0-9]+/', '_', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s);

        return trim($s, '_');
    }

    private function valorColuna(array $linha, array $mapa, array $chaves): string
    {
        foreach ($chaves as $chave) {
            if (isset($mapa[$chave]) && isset($linha[$mapa[$chave]])) {
                return trim((string) $linha[$mapa[$chave]]);
            }
        }

        return '';
    }
}

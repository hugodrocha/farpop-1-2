<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RemumeCodigoService
{
    public static function importarDeJson(string $caminhoJson): array
    {
        if (!is_file($caminhoJson)) {
            return ['atualizados' => 0, 'erro' => 'Arquivo não encontrado'];
        }

        $linhas = json_decode(file_get_contents($caminhoJson), true) ?: [];
        $atualizados = 0;

        foreach ($linhas as $linha) {
            $id = (int) ($linha['id'] ?? 0);
            $codigo = self::formatarCodigo((string) ($linha['codigo'] ?? ''));
            if ($id <= 0 || $codigo === '') {
                continue;
            }

            $atualizados += DB::table('medicamentos')->where('id', $id)->update([
                'codigo' => $codigo,
                'atualizado_em' => now(),
            ]);
        }

        return ['atualizados' => $atualizados, 'total' => count($linhas)];
    }

    public static function importarDeXmlExtraido(string $pastaExtraida): array
    {
        $shared = self::lerSharedStrings($pastaExtraida . '/xl/sharedStrings.xml');
        $rows = self::lerSheet($pastaExtraida . '/xl/worksheets/sheet2.xml', $shared);

        $atualizados = 0;
        foreach (array_slice($rows, 1) as $row) {
            $id = (int) ($row[0] ?? 0);
            $codigo = self::formatarCodigo((string) ($row[6] ?? ''));
            if ($id <= 0 || $codigo === '') {
                continue;
            }

            $atualizados += DB::table('medicamentos')->where('id', $id)->update([
                'codigo' => $codigo,
                'atualizado_em' => now(),
            ]);
        }

        return ['atualizados' => $atualizados, 'total' => max(0, count($rows) - 1)];
    }

    public static function formatarCodigo(string $codigo): string
    {
        $digitos = preg_replace('/\D/', '', $codigo);
        if (strlen($digitos) !== 11) {
            return trim($codigo);
        }

        return substr($digitos, 0, 4) . '.'
            . substr($digitos, 4, 2) . '.'
            . substr($digitos, 6, 3) . '-'
            . substr($digitos, 9, 2);
    }

    private static function lerSharedStrings(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $xml = simplexml_load_file($path);
        if (!$xml) {
            return [];
        }

        $strings = [];
        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
            } else {
                $texto = '';
                foreach ($si->r as $run) {
                    $texto .= (string) $run->t;
                }
                $strings[] = $texto;
            }
        }

        return $strings;
    }

    private static function lerSheet(string $path, array $sharedStrings): array
    {
        if (!is_file($path)) {
            return [];
        }

        $xml = simplexml_load_file($path);
        if (!$xml) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $linha = [];
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $col = preg_replace('/\d+/', '', $ref);
                $idx = self::colunaParaIndice($col);
                $linha[$idx] = self::valorCelula($cell, $sharedStrings);
            }
            if ($linha !== []) {
                ksort($linha);
                $rows[] = array_values($linha);
            }
        }

        return $rows;
    }

    private static function valorCelula($cell, array $sharedStrings): string
    {
        $type = (string) ($cell['t'] ?? '');
        if ($type === 's') {
            return $sharedStrings[(int) $cell->v] ?? '';
        }
        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        return (string) ($cell->v ?? '');
    }

    private static function colunaParaIndice(string $col): int
    {
        $col = strtoupper($col);
        $num = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $num = $num * 26 + (ord($col[$i]) - 64);
        }

        return $num - 1;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InventarioImportService
{
    /**
     * Importa contagens físicas a partir de CSV exportado pelo FARPOP.
     *
     * @return array{processados: int, ajustados: int, ignorados: int, erros: list<string>}
     */
    public static function importarDeArquivo(string $caminho, ?int $clinicaEscopo, ?int $usuarioId): array
    {
        LoteService::inicializar();

        $handle = fopen($caminho, 'r');
        if ($handle === false) {
            return ['processados' => 0, 'ajustados' => 0, 'ignorados' => 0, 'erros' => ['Não foi possível ler o arquivo.']];
        }

        $primeira = fgets($handle);
        if ($primeira === false) {
            fclose($handle);

            return ['processados' => 0, 'ajustados' => 0, 'ignorados' => 0, 'erros' => ['Arquivo vazio.']];
        }

        $primeira = self::removerBom($primeira);
        $delimitador = substr_count($primeira, ';') >= substr_count($primeira, ',') ? ';' : ',';
        $headers = str_getcsv($primeira, $delimitador);
        $mapa = self::mapearColunas($headers);

        if (!isset($mapa['qtd_contada'])) {
            fclose($handle);

            return ['processados' => 0, 'ajustados' => 0, 'ignorados' => 0, 'erros' => ['Coluna "Qtd contada" não encontrada no CSV.']];
        }

        $processados = 0;
        $ajustados = 0;
        $ignorados = 0;
        $erros = [];
        $linha = 1;

        while (($cols = fgetcsv($handle, 0, $delimitador)) !== false) {
            $linha++;
            if (self::linhaVazia($cols)) {
                continue;
            }

            $row = self::linhaAssociativa($mapa, $cols);
            $qtdRaw = trim((string) ($row['qtd_contada'] ?? ''));
            if ($qtdRaw === '') {
                $ignorados++;

                continue;
            }

            if (!is_numeric($qtdRaw) || (int) $qtdRaw < 0) {
                $erros[] = "Linha {$linha}: quantidade contada inválida ({$qtdRaw}).";
                continue;
            }

            $qtd = (int) $qtdRaw;
            $loteId = self::resolverLoteId($row, $clinicaEscopo);
            if ($loteId === null) {
                $erros[] = "Linha {$linha}: lote não identificado ({$row['medicamento']} / {$row['lote']}).";
                continue;
            }

            $processados++;

            try {
                $antes = (int) DB::table('estoque_lotes')->where('id', $loteId)->value('quantidade');
                LoteService::inventariarLote($loteId, $qtd, $usuarioId, 'Importação CSV inventário');
                if ($antes !== $qtd) {
                    $ajustados++;
                }
            } catch (\Throwable $e) {
                $erros[] = "Linha {$linha}: {$e->getMessage()}";
            }
        }

        fclose($handle);

        return compact('processados', 'ajustados', 'ignorados', 'erros');
    }

    /**
     * @param list<string> $headers
     * @return array<string, int>
     */
    private static function mapearColunas(array $headers): array
    {
        $mapa = [];
        foreach ($headers as $i => $h) {
            $norm = self::normalizar((string) $h);
            if (in_array($norm, ['id lote', 'lote id', 'lote_id', 'id'], true)) {
                $mapa['lote_id'] = $i;
            } elseif (in_array($norm, ['unidade', 'clinica', 'clínica'], true)) {
                $mapa['unidade'] = $i;
            } elseif ($norm === 'codigo' || $norm === 'código') {
                $mapa['codigo'] = $i;
            } elseif ($norm === 'medicamento') {
                $mapa['medicamento'] = $i;
            } elseif ($norm === 'lote') {
                $mapa['lote'] = $i;
            } elseif (in_array($norm, ['qtd contada', 'quantidade contada', 'contagem'], true)) {
                $mapa['qtd_contada'] = $i;
            }
        }

        return $mapa;
    }

    /**
     * @param array<string, int> $mapa
     * @param list<string|null> $cols
     * @return array<string, string>
     */
    private static function linhaAssociativa(array $mapa, array $cols): array
    {
        $out = [];
        foreach ($mapa as $chave => $idx) {
            $out[$chave] = trim((string) ($cols[$idx] ?? ''));
        }

        return $out;
    }

    /**
     * @param array<string, string> $row
     */
    private static function resolverLoteId(array $row, ?int $clinicaEscopo): ?int
    {
        if (!empty($row['lote_id']) && is_numeric($row['lote_id'])) {
            $id = (int) $row['lote_id'];
            $q = DB::table('estoque_lotes')->where('id', $id);
            if ($clinicaEscopo > 0) {
                $q->where('clinica_id', $clinicaEscopo);
            }

            return $q->exists() ? $id : null;
        }

        $clinicaId = $clinicaEscopo;
        if ($clinicaId <= 0 && !empty($row['unidade'])) {
            $clinicaId = (int) DB::table('clinicas')
                ->where('nome', $row['unidade'])
                ->where('ativo', 1)
                ->value('id');
        }

        if ($clinicaId <= 0) {
            return null;
        }

        $medQ = DB::table('medicamentos')->where('ativo', 1);
        if (!empty($row['codigo'])) {
            $medQ->where('codigo', $row['codigo']);
        } elseif (!empty($row['medicamento'])) {
            $medQ->where('nome', $row['medicamento']);
        } else {
            return null;
        }

        $medicamentoId = (int) $medQ->value('id');
        if ($medicamentoId <= 0) {
            return null;
        }

        $loteQ = DB::table('estoque_lotes')
            ->where('clinica_id', $clinicaId)
            ->where('medicamento_id', $medicamentoId);

        $loteTxt = $row['lote'] ?? '';
        if ($loteTxt !== '') {
            $loteQ->where('lote', $loteTxt);
        }

        $id = (int) $loteQ->orderByDesc('quantidade')->value('id');

        return $id > 0 ? $id : null;
    }

    private static function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));

        return str_replace(['_', '-'], ' ', $texto);
    }

    private static function removerBom(string $linha): string
    {
        if (str_starts_with($linha, "\xEF\xBB\xBF")) {
            return substr($linha, 3);
        }

        return $linha;
    }

    /**
     * @param list<string|null> $cols
     */
    private static function linhaVazia(array $cols): bool
    {
        foreach ($cols as $c) {
            if (trim((string) $c) !== '') {
                return false;
            }
        }

        return true;
    }
}

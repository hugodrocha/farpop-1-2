<?php

namespace App\Services;

class CodigoBarrasService
{
    /**
     * Gera SVG de um código de barras CODE128 (auto seleciona C quando é só dígito).
     * Retorna string SVG pronta para echo.
     */
    public static function code128Svg(string $data, int $larguraBarra = 2, int $altura = 80, bool $mostrarTexto = true): string
    {
        // Tabela com os 107 padrões + stop pattern (índice 106 = STOP)
        $patterns = [
            "11011001100","11001101100","11001100110","10010011000","10010001100",
            "10001001100","10011001000","10011000100","10001100100","11001001000",
            "11001000100","11000100100","10110011100","10011011100","10011001110",
            "10111001100","10011101100","10011100110","11001110010","11001011100",
            "11001001110","11011100100","11001110100","11101101110","11101001100",
            "11100101100","11100100110","11101100100","11100110100","11100110010",
            "11011011000","11011000110","11000110110","10100011000","10001011000",
            "10001000110","10110001000","10001101000","10001100010","11010001000",
            "11000101000","11000100010","10110111000","10110001110","10001101110",
            "10111011000","10111000110","10001110110","11101110110","11010001110",
            "11000101110","11011101000","11011100010","11011101110","11101011000",
            "11101000110","11100010110","11101101000","11101100010","11100011010",
            "11101111010","11001000010","11110001010","10100110000","10100001100",
            "10010110000","10010000110","10000101100","10000100110","10110010000",
            "10110000100","10011010000","10011000010","10000110100","10000110010",
            "11000010010","11001010000","11110111010","11000010100","10001111010",
            "10100111100","10010111100","10010011110","10111100100","10011110100",
            "10011110010","11110100100","11110010100","11110010010","11011011110",
            "11011110110","11110110110","10101111000","10100011110","10001011110",
            "10111101000","10111100010","11110101000","11110100010","10111011110",
            "10111101110","11101011110","11110101110","11010000100","11010010000",
            "11010011100","11000111010"
        ];
        $stopPattern = "11000111010"; // 106 (mas vamos usar 107 abaixo)
        // Para CODE128 o stop final é 11000111010 + "11" (2 bits extras) = 13 módulos.
        // Vamos usar a sequência padronizada de fim com "11" adicional.

        // Se for tudo dígito e tamanho par, usamos CODE128-C (mais compacto)
        $isNumPar = (preg_match('/^\d+$/', $data) === 1) && (strlen($data) % 2 === 0);

        $codes = [];
        if ($isNumPar) {
            $codes[] = 105; // START C
            for ($i = 0; $i < strlen($data); $i += 2) {
                $codes[] = (int)substr($data, $i, 2);
            }
        } else {
            // CODE128-B (ASCII 32-127)
            $codes[] = 104; // START B
            $len = strlen($data);
            for ($i = 0; $i < $len; $i++) {
                $ord = ord($data[$i]);
                if ($ord < 32 || $ord > 126) continue;
                $codes[] = $ord - 32;
            }
        }

        // Cálculo do checksum
        $sum = $codes[0];
        for ($i = 1; $i < count($codes); $i++) {
            $sum += $codes[$i] * $i;
        }
        $check = $sum % 103;
        $codes[] = $check;
        $codes[] = 106; // STOP

        // Monta string binária
        $bin = "";
        foreach ($codes as $c) {
            $bin .= $patterns[$c];
        }
        $bin .= "11"; // 2 bits finais do stop

        $larguraTotal = strlen($bin) * $larguraBarra;
        $alturaTotal  = $altura + ($mostrarTexto ? 22 : 0);

        $svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $larguraTotal . '" height="' . $alturaTotal . '" viewBox="0 0 ' . $larguraTotal . ' ' . $alturaTotal . '" shape-rendering="crispEdges">';
        $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';
        $x = 0;
        for ($i = 0; $i < strlen($bin); $i++) {
            if ($bin[$i] === "1") {
                $svg .= '<rect x="' . $x . '" y="0" width="' . $larguraBarra . '" height="' . $altura . '" fill="#000"/>';
            }
            $x += $larguraBarra;
        }
        if ($mostrarTexto) {
            $svg .= '<text x="' . ($larguraTotal/2) . '" y="' . ($altura + 16) . '" text-anchor="middle" font-family="monospace" font-size="14" fill="#000" letter-spacing="2">' . htmlspecialchars($data) . '</text>';
        }
        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Valor a codificar na etiqueta de lote (prioriza EAN/código de barras do medicamento).
     */
    public static function valorEtiquetaLote(object $lote): string
    {
        $cb = trim((string) ($lote->codigo_barras ?? ''));
        if ($cb !== '') {
            return $cb;
        }

        $codigo = trim((string) ($lote->codigo ?? ''));
        if ($codigo !== '') {
            return $codigo;
        }

        return 'LOTE' . (int) ($lote->id ?? 0);
    }
}

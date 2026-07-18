<?php

namespace App\Services;

class GraficoService
{
    /**
     * Gera um gráfico de PIZZA em SVG.
     * $dados: array de ["label"=>string, "valor"=>number, "cor"=>"#rrggbb"]
     * $opts:  largura, altura, mostrarLegenda
     */
    public static function graficoPizzaSvg(array $dados, array $opts = []): string
    {
        $largura  = $opts["largura"] ?? 380;
        $altura   = $opts["altura"]  ?? 260;
        $mostrarLegenda = $opts["legenda"] ?? true;
        $titulo   = $opts["titulo"] ?? "";

        // raio + centro
        $cx = $mostrarLegenda ? 110 : (int)($largura/2);
        $cy = (int)($altura/2) + ($titulo ? 12 : 0);
        $r  = min($cx, $cy) - 16;

        $total = 0;
        foreach ($dados as $d) $total += (float)$d["valor"];

        $svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$largura.' '.$altura.'" width="100%" height="auto" style="font-family:Inter,Segoe UI,sans-serif;">';
        if ($titulo !== "") {
            $svg .= '<text x="'.((int)($largura/2)).'" y="18" text-anchor="middle" font-size="13" font-weight="700" fill="#0f172a">'.htmlspecialchars($titulo).'</text>';
        }

        if ($total <= 0) {
            $svg .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$r.'" fill="#e5e7eb"/>';
            $svg .= '<text x="'.$cx.'" y="'.($cy+5).'" text-anchor="middle" font-size="13" fill="#6b7280">Sem dados</text>';
            $svg .= '</svg>';
            return $svg;
        }

        $anguloInicio = -M_PI / 2; // começa em 12h
        foreach ($dados as $i => $d) {
            $valor = (float)$d["valor"];
            if ($valor <= 0) continue;
            $fracao = $valor / $total;
            $anguloFim = $anguloInicio + $fracao * 2 * M_PI;

            $x1 = $cx + $r * cos($anguloInicio);
            $y1 = $cy + $r * sin($anguloInicio);
            $x2 = $cx + $r * cos($anguloFim);
            $y2 = $cy + $r * sin($anguloFim);
            $largeArc = ($fracao > 0.5) ? 1 : 0;

            $cor = $d["cor"] ?? "#0d9488";
            if (count($dados) === 1) {
                $svg .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$r.'" fill="'.$cor.'"/>';
            } else {
                $path = sprintf(
                    'M %.2f %.2f L %.2f %.2f A %d %d 0 %d 1 %.2f %.2f Z',
                    $cx, $cy, $x1, $y1, $r, $r, $largeArc, $x2, $y2
                );
                $svg .= '<path d="'.$path.'" fill="'.$cor.'" stroke="#fff" stroke-width="2"/>';
            }

            // rótulo de % dentro da fatia (apenas se >5%)
            if ($fracao >= 0.05) {
                $angMeio = ($anguloInicio + $anguloFim) / 2;
                $tx = $cx + ($r * 0.6) * cos($angMeio);
                $ty = $cy + ($r * 0.6) * sin($angMeio);
                $pct = round($fracao * 100);
                $svg .= '<text x="'.round($tx).'" y="'.round($ty+4).'" text-anchor="middle" font-size="11" font-weight="700" fill="#fff" style="pointer-events:none;text-shadow:0 1px 2px rgba(0,0,0,0.4);">'.$pct.'%</text>';
            }

            $anguloInicio = $anguloFim;
        }

        // legenda
        if ($mostrarLegenda) {
            $lx = $cx + $r + 24;
            $ly = max(20, $cy - count($dados) * 9);
            foreach ($dados as $d) {
                $cor = $d["cor"] ?? "#0d9488";
                $label = $d["label"] ?? "";
                $valor = (float)$d["valor"];
                $pct = $total > 0 ? round(($valor/$total)*100) : 0;
                $svg .= '<rect x="'.$lx.'" y="'.$ly.'" width="12" height="12" rx="2" fill="'.$cor.'"/>';
                $textoCorrido = $label.' — '.number_format($valor,0,',','.').' ('.$pct.'%)';
                if (mb_strlen($textoCorrido) > 38) $textoCorrido = mb_substr($textoCorrido, 0, 35).'…';
                $svg .= '<text x="'.($lx+18).'" y="'.($ly+10).'" font-size="11" fill="#334155">'.htmlspecialchars($textoCorrido).'</text>';
                $ly += 18;
                if ($ly > $altura - 14) break;
            }
        }
        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Gera um gráfico de BARRAS HORIZONTAIS em SVG (ideal para top-N).
     * $dados: array de ["label"=>string, "valor"=>number, "cor"=>"#rrggbb", "sub"=>?string]
     */
    public static function graficoBarrasSvg(array $dados, array $opts = []): string
    {
        $largura  = $opts["largura"] ?? 480;
        $alturaItem = $opts["alturaItem"] ?? 28;
        $padTop = ($opts["titulo"] ?? "") !== "" ? 30 : 10;
        $altura   = $padTop + count($dados) * $alturaItem + 10;
        if (empty($dados)) { $altura = max(80, $padTop + 60); }
        $larguraLabel = $opts["larguraLabel"] ?? 160;
        $titulo   = $opts["titulo"] ?? "";

        $max = 0;
        foreach ($dados as $d) if ((float)$d["valor"] > $max) $max = (float)$d["valor"];
        if ($max <= 0) $max = 1;

        $svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$largura.' '.$altura.'" width="100%" height="auto" style="font-family:Inter,Segoe UI,sans-serif;">';
        if ($titulo !== "") {
            $svg .= '<text x="10" y="18" font-size="13" font-weight="700" fill="#0f172a">'.htmlspecialchars($titulo).'</text>';
        }
        if (empty($dados)) {
            $svg .= '<text x="'.(int)($largura/2).'" y="'.((int)($altura/2)+8).'" text-anchor="middle" font-size="13" fill="#6b7280">Sem dados</text>';
            $svg .= '</svg>';
            return $svg;
        }

        $larguraBarMax = $largura - $larguraLabel - 60;
        $y = $padTop;
        foreach ($dados as $d) {
            $valor = (float)$d["valor"];
            $cor = $d["cor"] ?? "#0d9488";
            $label = $d["label"] ?? "";
            $sub = $d["sub"] ?? "";
            $w = max(2, ($valor / $max) * $larguraBarMax);

            // label
            $lblExibir = $label;
            if (mb_strlen($lblExibir) > 22) $lblExibir = mb_substr($lblExibir, 0, 20).'…';
            $svg .= '<text x="'.($larguraLabel-6).'" y="'.($y+14).'" text-anchor="end" font-size="12" font-weight="600" fill="#0f172a">'.htmlspecialchars($lblExibir).'</text>';
            if ($sub !== "") {
                $subShow = mb_strlen($sub) > 22 ? (mb_substr($sub,0,20).'…') : $sub;
                $svg .= '<text x="'.($larguraLabel-6).'" y="'.($y+24).'" text-anchor="end" font-size="9" fill="#94a3b8">'.htmlspecialchars($subShow).'</text>';
            }

            // barra
            $svg .= '<rect x="'.$larguraLabel.'" y="'.($y+5).'" width="'.$larguraBarMax.'" height="14" rx="3" fill="#f1f5f9"/>';
            $svg .= '<rect x="'.$larguraLabel.'" y="'.($y+5).'" width="'.round($w,2).'" height="14" rx="3" fill="'.$cor.'"/>';
            // valor
            $svg .= '<text x="'.($larguraLabel + $w + 6).'" y="'.($y+16).'" font-size="11" font-weight="700" fill="#0f172a">'.number_format($valor,0,',','.').'</text>';

            $y += $alturaItem;
        }
        $svg .= '</svg>';
        return $svg;
    }
}

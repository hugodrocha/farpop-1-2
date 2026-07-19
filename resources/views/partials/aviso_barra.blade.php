@if(!empty($farpopAvisos) && count($farpopAvisos) > 0)
    @php
        $textos = [];
        foreach ($farpopAvisos as $aviso) {
            $cor = \App\Services\AvisoBarraService::corPrioridadeAviso($aviso->prioridade ?? 'info');
            $textos[] = '<span style="display:inline-flex;align-items:center;gap:8px;margin-right:48px;">'
                . ($cor['label'] ?? '') . ' ' . e($aviso->mensagem) . '</span>';
        }
        $marquee = implode('', $textos);
        $primeiro = $farpopAvisos[0];
        $bg = \App\Services\AvisoBarraService::corPrioridadeAviso($primeiro->prioridade ?? 'info')['bg'] ?? 'linear-gradient(135deg,#064e3b,#14b8a6)';
    @endphp
    <div class="farpop-barra-avisos" style="background: {{ $bg }}; color: #fff;">
        <div class="farpop-barra-marquee px-3 py-2 small fw-medium">
            {!! $marquee !!}{!! $marquee !!}
        </div>
    </div>
@endif

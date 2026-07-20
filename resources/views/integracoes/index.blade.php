@extends('layouts.app')

@section('title', 'Integrações')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-plug me-2"></i>Integrações</h1>
        <p class="farpop-page-subtitle">Conexões externas e exportações do FARPOP. O Hórus permanece apenas como exportação legada.</p>
    </div>

    @include('partials.flash')

    <div class="row g-3">
        @foreach($integracoes as $int)
            @php
                $badge = match($int['status']) {
                    'ativo' => 'success',
                    'legado' => 'secondary',
                    default => 'warning',
                };
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h2 class="h6 mb-0"><i class="fas {{ $int['icone'] }} me-2 text-primary"></i>{{ $int['label'] }}</h2>
                            <span class="badge bg-{{ $badge }}">{{ $int['status_label'] }}</span>
                        </div>
                        <p class="text-muted small mb-2">{{ $int['descricao'] }}</p>
                        <p class="small text-muted mb-auto">{{ $int['detalhe'] }}</p>
                        <div class="mt-3">
                            <a href="{{ $int['url'] }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-arrow-right me-1"></i> {{ $int['url_label'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

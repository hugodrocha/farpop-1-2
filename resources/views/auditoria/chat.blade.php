@extends('layouts.app')

@section('title', 'Auditoria do chat')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-comments me-2"></i>Auditoria do chat</h1>
            <p class="farpop-page-subtitle">Histórico de mensagens trocadas no chat interno (últimas 300).</p>
        </div>
        <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Voltar à auditoria</a>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Remetente</th>
                            <th>Destinatário</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mensagens as $msg)
                            <tr>
                                <td class="text-nowrap small">{{ $msg->enviado_em ? \Carbon\Carbon::parse($msg->enviado_em)->format('d/m/Y H:i:s') : '—' }}</td>
                                <td>{{ $msg->remetente ?? '—' }}</td>
                                <td>
                                    @if($msg->destinatario)
                                        {{ $msg->destinatario }}
                                    @else
                                        <span class="badge bg-info">Canal geral</span>
                                    @endif
                                </td>
                                <td class="small">{{ $msg->mensagem }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                    Nenhuma mensagem registrada ou tabela de chat não disponível.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

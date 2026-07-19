@extends('layouts.app')

@section('title', 'Auditoria do chat')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-comments me-2"></i>Auditoria do chat</h1>
            <p class="farpop-page-subtitle">Histórico auditado de mensagens privadas e em grupo (últimas 300).</p>
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
                            <th>Conversa</th>
                            <th>Destinatário / participantes</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mensagens as $msg)
                            @php
                                $cid = (int) ($msg->conversa_id ?? 0);
                                $tipo = $msg->conversa_tipo ?? null;
                                $participantes = $cid > 0 ? ($membrosPorConversa[$cid] ?? []) : [];
                            @endphp
                            <tr>
                                <td class="text-nowrap small">{{ $msg->enviado_em ? \Carbon\Carbon::parse($msg->enviado_em)->format('d/m/Y H:i:s') : '—' }}</td>
                                <td>{{ $msg->remetente ?? '—' }}</td>
                                <td class="small">
                                    @if($tipo === 'grupo')
                                        <span class="badge bg-info"><i class="fas fa-users me-1"></i>Grupo</span>
                                        {{ $msg->conversa_titulo ?: 'Sem título' }}
                                    @elseif($tipo === 'privada')
                                        <span class="badge bg-secondary">Privada</span>
                                    @elseif($msg->destinatario)
                                        <span class="badge bg-secondary">Privada</span>
                                    @else
                                        <span class="badge bg-light text-dark">Legado</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($tipo === 'grupo' && count($participantes))
                                        {{ implode(', ', $participantes) }}
                                    @elseif($msg->destinatario)
                                        {{ $msg->destinatario }}
                                    @elseif($tipo === 'privada' && count($participantes))
                                        {{ implode(' ↔ ', $participantes) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small">{{ $msg->mensagem }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
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

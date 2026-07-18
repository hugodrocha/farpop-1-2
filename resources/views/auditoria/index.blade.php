@extends('layouts.app')

@section('title', 'Auditoria')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-shield-alt me-2"></i>Auditoria</h1>
            <p class="farpop-page-subtitle">Histórico de ações registradas no sistema.</p>
        </div>
        <a href="{{ route('auditoria.chat') }}" class="btn btn-outline-primary"><i class="fas fa-comments me-1"></i> Auditoria do chat</a>
    </div>

    @include('partials.flash')

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('auditoria.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Ação</label>
                    <input type="text" name="acao" class="form-control" value="{{ request('acao') }}" placeholder="Ex: LOGIN, SAIDA...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Usuário</label>
                    <input type="text" name="usuario" class="form-control" value="{{ request('usuario') }}" placeholder="Login ou nome">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary"><i class="fas fa-list me-2"></i>Registros (últimos 200)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Alvo</th>
                            <th>Detalhes</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $registro)
                            <tr>
                                <td class="text-nowrap small">{{ $registro->criado_em ? \Carbon\Carbon::parse($registro->criado_em)->format('d/m/Y H:i:s') : '—' }}</td>
                                <td>
                                    <div>{{ $registro->nome_responsavel ?: '—' }}</div>
                                    <div class="text-muted small">{{ $registro->usuario_responsavel }}</div>
                                </td>
                                <td><span class="badge bg-primary">{{ $registro->acao }}</span></td>
                                <td class="small">{{ $registro->alvo ?: '—' }}</td>
                                <td class="text-muted small">{{ Str::limit($registro->detalhes ?? '—', 120) }}</td>
                                <td class="text-muted small">{{ $registro->ip ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum registro encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($ehSuperuser)
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-2"></i>Ações críticas (superuser)
            </div>
            <div class="card-body">
                <p class="text-muted small">Estas ações são irreversíveis. Digite a frase de confirmação exatamente como indicado.</p>

                <form method="POST" action="{{ route('auditoria.critica') }}" class="mb-4" onsubmit="return confirm('Confirma ZERAR TODO O ESTOQUE? Esta ação não pode ser desfeita.')">
                    @csrf
                    <input type="hidden" name="acao_critica" value="zerar_estoque">
                    <h3 class="h6 text-danger">Zerar todo o estoque</h3>
                    <div class="mb-2">
                        <label class="form-label small">Frase: <code>{{ \App\Http\Controllers\AuditoriaController::FRASE_ZERAR_TUDO }}</code></label>
                        <input type="text" name="frase_confirmacao" class="form-control" placeholder="Digite a frase exata">
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">Zerar todo estoque</button>
                </form>

                <form method="POST" action="{{ route('auditoria.critica') }}" class="mb-4" onsubmit="return confirm('Confirma zerar o estoque desta clínica?')">
                    @csrf
                    <input type="hidden" name="acao_critica" value="zerar_estoque_clinica">
                    <h3 class="h6 text-danger">Zerar estoque de uma clínica</h3>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <select name="clinica_id" class="form-select form-select-sm" required>
                                <option value="">Selecione a clínica...</option>
                                @foreach($clinicas as $c)
                                    <option value="{{ $c->id }}">{{ $c->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="frase_confirmacao" class="form-control form-control-sm" placeholder="ZERAR ESTOQUE DA CLINICA: NOME">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">Zerar clínica</button>
                </form>

                <form method="POST" action="{{ route('auditoria.critica') }}" onsubmit="return confirm('Confirma zerar o estoque deste medicamento em todas as clínicas?')">
                    @csrf
                    <input type="hidden" name="acao_critica" value="zerar_estoque_medicamento">
                    <h3 class="h6 text-danger">Zerar estoque de um medicamento (todas as clínicas)</h3>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <select name="medicamento_id" class="form-select form-select-sm" required>
                                <option value="">Selecione o medicamento...</option>
                                @foreach($medicamentos as $m)
                                    <option value="{{ $m->id }}">{{ $m->nome }} {{ $m->dosagem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="frase_confirmacao" class="form-control form-control-sm" placeholder="ZERAR ESTOQUE DO MEDICAMENTO: NOME">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">Zerar medicamento</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

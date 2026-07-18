@extends('layouts.app')

@section('title', 'Configurações')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-cog me-2"></i>Configurações</h1>
        <p class="farpop-page-subtitle">Alertas de estoque, chat interno e integração PEC.</p>
    </div>

    @include('partials.flash')

    @if(session('pec_teste'))
        @php $teste = session('pec_teste'); @endphp
        <div class="alert {{ ($teste['ok'] ?? false) ? 'alert-success' : 'alert-warning' }} mb-4">
            <strong>Teste PEC:</strong> {{ $teste['motivo'] ?? '—' }}
            @if(!empty($teste['teste_cpf']))
                <hr class="my-2">
                <pre class="mb-0 small">{{ json_encode($teste['teste_cpf'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @endif
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary"><i class="fas fa-bell me-2"></i>Alertas de estoque</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('configuracoes.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Alerta amarelo (% do estoque mínimo)</label>
                            <input type="number" name="alerta_amarelo_percentual" class="form-control"
                                   value="{{ old('alerta_amarelo_percentual', $configs->get('alerta_amarelo_percentual')?->valor ?? 100) }}"
                                   min="1" max="500" required>
                            <div class="form-text">Itens com quantidade ≤ este percentual do mínimo entram em atenção.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alerta vermelho (% do estoque mínimo)</label>
                            <input type="number" name="alerta_vermelho_percentual" class="form-control"
                                   value="{{ old('alerta_vermelho_percentual', $configs->get('alerta_vermelho_percentual')?->valor ?? 50) }}"
                                   min="1" max="500" required>
                            <div class="form-text">Itens com quantidade ≤ este percentual do mínimo são críticos.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Chat interno</label>
                            @php $chatVal = old('chat_habilitado', $configs->get('chat_habilitado')?->valor ?? '1'); @endphp
                            <select name="chat_habilitado" class="form-select" required>
                                <option value="1" {{ $chatVal == '1' ? 'selected' : '' }}>Habilitado</option>
                                <option value="0" {{ $chatVal == '0' ? 'selected' : '' }}>Desabilitado</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar configurações</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary"><i class="fas fa-database me-2"></i>Integração PEC (e-SUS)</div>
                <div class="card-body">
                    <div class="mb-4">
                        <span class="text-muted d-block small">Status atual</span>
                        @if($pecStatus['ok'] ?? false)
                            <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> Disponível</span>
                            <p class="small text-muted mt-2 mb-0">{{ $pecStatus['motivo'] ?? '' }}</p>
                        @else
                            <span class="badge bg-danger fs-6"><i class="fas fa-times-circle me-1"></i> Indisponível</span>
                            <p class="small text-muted mt-2 mb-0">{{ $pecStatus['motivo'] ?? 'Integração não configurada.' }}</p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('configuracoes.pec-teste') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">CPF para teste (opcional)</label>
                            <input type="text" name="cpf_teste" class="form-control" maxlength="14" placeholder="000.000.000-00">
                            <div class="form-text">Informe um CPF para testar a busca de cidadão no PEC.</div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary"><i class="fas fa-vial me-1"></i> Testar conexão PEC</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><i class="fas fa-list me-2"></i>Parâmetros cadastrados</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Chave</th>
                                    <th>Valor</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($configs as $cfg)
                                    <tr>
                                        <td><code>{{ $cfg->chave }}</code></td>
                                        <td>{{ $cfg->valor }}</td>
                                        <td class="text-muted small">{{ $cfg->descricao ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">Nenhuma configuração.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

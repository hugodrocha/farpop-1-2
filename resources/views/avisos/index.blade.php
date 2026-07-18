@extends('layouts.app')

@section('title', 'Avisos da barra')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-bullhorn me-2"></i>Avisos da barra</h1>
        <p class="farpop-page-subtitle">Gerencie mensagens exibidas na barra inferior do sistema.</p>
    </div>

    @include('partials.flash')

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-primary"><i class="fas fa-plus me-2"></i>Novo aviso</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('avisos.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Mensagem</label>
                            <textarea name="mensagem" class="form-control" rows="3" maxlength="2000" required>{{ old('mensagem') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prioridade</label>
                            <select name="prioridade" class="form-select" required>
                                <option value="info" {{ old('prioridade') === 'info' ? 'selected' : '' }}>Informação</option>
                                <option value="aviso" {{ old('prioridade') === 'aviso' ? 'selected' : '' }}>Aviso</option>
                                <option value="alerta" {{ old('prioridade') === 'alerta' ? 'selected' : '' }}>Alerta</option>
                                <option value="urgente" {{ old('prioridade') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Exibir para</label>
                            <select name="para_todas" id="paraTodas" class="form-select" required>
                                <option value="1" {{ old('para_todas', '1') == '1' ? 'selected' : '' }}>Todas as clínicas</option>
                                <option value="0" {{ old('para_todas') === '0' ? 'selected' : '' }}>Clínicas específicas</option>
                            </select>
                        </div>
                        <div class="mb-3" id="boxClinicas" style="{{ old('para_todas') === '0' ? '' : 'display:none' }}">
                            <label class="form-label">Clínicas</label>
                            <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto;">
                                @foreach($clinicas as $clinica)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="clinicas[]" value="{{ $clinica->id }}"
                                               id="clinica_{{ $clinica->id }}"
                                               {{ is_array(old('clinicas')) && in_array($clinica->id, old('clinicas')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="clinica_{{ $clinica->id }}">{{ $clinica->nome }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Encerrar em</label>
                            <input type="datetime-local" name="termina_em" class="form-control"
                                   value="{{ old('termina_em', now()->addDays(7)->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-bullhorn me-1"></i> Publicar aviso</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-primary"><i class="fas fa-list me-2"></i>Avisos recentes</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Mensagem</th>
                                    <th>Prioridade</th>
                                    <th>Período</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($avisos as $aviso)
                                    @php
                                        $prioClass = match($aviso->prioridade) {
                                            'urgente' => 'danger',
                                            'alerta' => 'warning',
                                            'aviso' => 'info',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr class="{{ !$aviso->ativo ? 'table-secondary' : '' }}">
                                        <td>
                                            <div class="small fw-medium">{{ Str::limit($aviso->mensagem, 80) }}</div>
                                            <div class="text-muted small">por {{ $aviso->autor }}</div>
                                        </td>
                                        <td><span class="badge bg-{{ $prioClass }}">{{ ucfirst($aviso->prioridade) }}</span></td>
                                        <td class="small text-nowrap">
                                            {{ $aviso->inicia_em ? \Carbon\Carbon::parse($aviso->inicia_em)->format('d/m/Y H:i') : '—' }}
                                            <br>
                                            <span class="text-muted">até {{ $aviso->termina_em ? \Carbon\Carbon::parse($aviso->termina_em)->format('d/m/Y H:i') : '—' }}</span>
                                        </td>
                                        <td>
                                            @if($aviso->ativo)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-secondary">Encerrado</span>
                                            @endif
                                            @if($aviso->para_todas)
                                                <span class="badge bg-light text-dark">Todas</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($aviso->ativo)
                                                <form method="POST" action="{{ route('avisos.encerrar', $aviso->id) }}" class="d-inline" onsubmit="return confirm('Encerrar este aviso?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Encerrar">
                                                        <i class="fas fa-stop"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Nenhum aviso cadastrado.</td>
                                    </tr>
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

@push('scripts')
<script>
document.getElementById('paraTodas').addEventListener('change', function () {
    document.getElementById('boxClinicas').style.display = this.value === '0' ? '' : 'none';
});
</script>
@endpush

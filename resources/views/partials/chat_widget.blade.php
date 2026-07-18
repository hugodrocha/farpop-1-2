@if(\App\Services\ConfigService::chatHabilitado() && session()->has('usuario_id'))
    <a href="{{ route('chat.index') }}" class="farpop-chat-fab" title="Chat FARPOP" aria-label="Abrir chat">
        <i class="fas fa-comments"></i>
        @if(($farpopChatNaoLidas ?? 0) > 0)
            <span class="farpop-chat-badge">{{ $farpopChatNaoLidas > 99 ? '99+' : $farpopChatNaoLidas }}</span>
        @endif
    </a>
@endif

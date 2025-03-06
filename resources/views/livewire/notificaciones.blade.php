<div class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" id="notificacionesDropdown" role="button"
        data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell fa-lg"></i>
        @if ($totalNoLeidas > 0)
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                {{ $totalNoLeidas }}
            </span>
        @endif
    </a>

    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificacionesDropdown">
        <li class="dropdown-header">Notificaciones</li>
        @forelse ($notificaciones as $notificacion)
            <li>
                <a class="dropdown-item" href="{{ $notificacion->data['url'] ?? '#' }}">
                    <small class="text-muted">{{ $notificacion->created_at->diffForHumans() }}</small>
                    <br>
                    {{ $notificacion->data['mensaje'] }}
                </a>
            </li>
        @empty
            <li><a class="dropdown-item text-center text-muted">No hay notificaciones</a></li>
        @endforelse

        @if ($totalNoLeidas > 0)
            <li><hr class="dropdown-divider"></li>
            <li>
                <button class="dropdown-item text-center" wire:click="marcarComoLeidas">
                    Marcar todas como leídas
                </button>
            </li>
        @endif
    </ul>
</div>
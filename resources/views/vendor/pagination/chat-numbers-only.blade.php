@if ($paginator->hasPages())
    <div class="chat-pagination-info">
        Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
    </div>

    <nav class="chat-pagination-nav" role="navigation" aria-label="Paginacion chat">
        @for ($page = 1; $page <= $paginator->lastPage(); $page++)
            @if ($page === $paginator->currentPage())
                <span class="chat-pagination-page active" aria-current="page">{{ $page }}</span>
            @else
                <button
                    type="button"
                    class="chat-pagination-page"
                    wire:click="gotoPage({{ $page }})"
                    aria-label="Ir a pagina {{ $page }}"
                >
                    {{ $page }}
                </button>
            @endif
        @endfor
    </nav>
@endif

@if ($paginator->hasPages())
<nav class="pagination" aria-label="Phân trang thực đơn">
    @if ($paginator->onFirstPage())
        <span aria-disabled="true">← Trước</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Trước</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))<span>{{ $element }}</span>@endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page === $paginator->currentPage())<span class="current" aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next">Sau →</a>
    @else
        <span aria-disabled="true">Sau →</span>
    @endif
</nav>
@endif

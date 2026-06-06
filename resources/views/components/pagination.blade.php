@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-meta text-muted small">
            Page <strong>{{ $paginator->currentPage() }}</strong> of <strong>{{ $paginator->lastPage() }}</strong>
            <span class="d-none d-sm-inline">• Showing {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}</span>
        </div>

        <ul class="pagination pagination-pages mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Previous page">
                    <span class="page-link">&laquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&laquo;</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Next page">
                    <span class="page-link">&raquo;</span>
                </li>
            @endif
        </ul>

        <div class="pagination-mobile-actions d-flex d-sm-none gap-2 w-100">
            @if ($paginator->onFirstPage())
                <span class="btn btn-light rounded-pill flex-fill disabled">Previous</span>
            @else
                <a class="btn btn-outline-primary rounded-pill flex-fill" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="btn btn-primary rounded-pill flex-fill" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="btn btn-light rounded-pill flex-fill disabled">Next</span>
            @endif
        </div>
    </nav>
@endif

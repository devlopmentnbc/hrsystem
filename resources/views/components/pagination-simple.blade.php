@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-meta text-muted small">
            @if(method_exists($paginator, 'currentPage'))
                Page <strong>{{ $paginator->currentPage() }}</strong>
            @endif
        </div>

        <ul class="pagination mb-0 d-none d-sm-flex">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">Previous</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a></li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">Next</span></li>
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

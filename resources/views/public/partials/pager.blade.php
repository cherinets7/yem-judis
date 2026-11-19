@if ($paginator->hasPages())
    <nav class="mt-4" aria-label="ገጾች">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item @if($paginator->onFirstPage()) disabled @endif">
                <a class="page-link" href="{{ $paginator->previousPageUrl() ?: '#' }}">ቀዳሚ</a>
            </li>
            <li class="page-item disabled">
                <span class="page-link">ገጽ {{ $paginator->currentPage() }} ከ {{ $paginator->lastPage() }}</span>
            </li>
            <li class="page-item @if(! $paginator->hasMorePages()) disabled @endif">
                <a class="page-link" href="{{ $paginator->nextPageUrl() ?: '#' }}">ቀጣይ</a>
            </li>
        </ul>
    </nav>
@endif

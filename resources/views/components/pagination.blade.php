<div class="d-flex justify-content-between align-items-center">
    @if (!empty($pagesTotal))
        <nav aria-label="Page navigation example">
            <ul class="pagination mb-0">
                @if ($currentPage > 1)
                    <li class="page-item">
                        <a class="page-link" wire:click="$set('currentPage', {{ $currentPage - 1 }})" wire:key="page-prev">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0" />
                            </svg>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0" />
                            </svg>
                        </span>
                    </li>
                @endif

                @if ($startPage > 1)
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click="$set('currentPage', {{ 1 }})" wire:key="page-1">
                            1
                        </button>
                    </li>
                @endif
                @if ($startPage > 2)
                    <li class="page-item disabled">
                        <span class="page-link">
                            ...
                        </span>
                    </li>
                @endif


                @for ($i = $startPage; $i <= $endPage; $i++)
                    <li class="page-item">
                        <button type="button" class="page-link @if ($i == $currentPage) active @endif" wire:key="page-{{ $i }}" @if ($i != $currentPage) wire:click="$set('currentPage', {{ $i }})" @endif>
                            {{ number_format($i,0, '.', ' ') }}
                        </button>
                    </li>
                @endfor

                @if ($pagesTotal > $endPage)
                    @if ($pagesTotal > $endPage + 1)
                        <li class="page-item disabled">
                            <span class="page-link">
                                ...
                            </span>
                        </li>
                    @endif
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click="$set('currentPage', {{ $pagesTotal }})" wire:key="page-{{ $pagesTotal }}">
                            {{ number_format($pagesTotal,0, '.', ' ') }}
                        </button>
                    </li>
                @endif

                @if ($currentPage < $pagesTotal)
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click="$set('currentPage', {{ $currentPage + 1 }})" wire:key="page-next">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                            </svg>
                        </button>
                    </li>
                @endif
            </ul>
        </nav>
        <div class="d-flex align-items-center text-nowrap">
            <span class="me-4">
                {{ number_format($itemsPerPage * ($currentPage - 1) + 1,0, '.', ' ') }} -
                @if ($currentPage == $endPage)
                    {{ number_format($itemsTotal % ($itemsPerPage * $endPage),0, '.', ' ') }}
                @else
                    {{ number_format($itemsPerPage * $currentPage,0, '.', ' ') }}
                @endif
                of {{ number_format($itemsTotal,0, '.', ' ') }}
            </span>
            <span class="me-2">Per page: </span>
            <select class="form-select" wire:model.live="itemsPerPage">
                @foreach ([10, 20, 50, 100, 1000] as $itemsPerPage)
                    <option value="{{ $itemsPerPage }}">
                        {{ $itemsPerPage != 0 ? number_format($itemsPerPage, 0, '.', ' ') : 'custom' }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
</div>

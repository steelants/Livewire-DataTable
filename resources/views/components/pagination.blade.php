<div class="d-flex justify-content-between align-items-center">
    @if (!empty($pagesTotal))
        <nav aria-label="Page navigation example">
            <ul class="pagination mb-0">
                @if ($currentPage > 1)
                    <li class="page-item">
                        <a class="page-link" wire:click.prevent="$set('currentPage', {{ $currentPage - 1 }})">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                @endif

                @if ($startPage > 1)
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click.prevent="$set('currentPage', {{ 1 }})">
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
                        <button type="button" class="page-link @if ($i == $currentPage) active @endif" @if ($i != $currentPage) wire:click.prevent="$set('currentPage', {{ $i }})" @endif>
                            {{ $i }}
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
                        <button type="button" class="page-link" wire:click.prevent="$set('currentPage', {{ $pagesTotal }})">
                            {{ $pagesTotal }}
                        </button>
                    </li>
                @endif

                @if ($currentPage < $pagesTotal)
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click.prevent="$set('currentPage', {{ $currentPage + 1 }})">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </li>
                @endif
            </ul>
        </nav>
        <div class="d-flex align-items-center text-nowrap">
            <span class="me-4">
                {{ 1 + $itemsPerPage * $currentPage }} -
                @if ($currentPage == $endPage)
                    {{ $itemsTotal % ($itemsPerPage * $endPage) }}
                @else
                    {{ $itemsPerPage * ($currentPage + 1) }}
                @endif
                of {{ $itemsTotal }}
            </span>
            <span class="me-2">Per page: </span>
            <select class="form-select" wire:model="itemsPerPage">
                @foreach ([10, 20, 50, 100, 1000] as $itemsPerPage)
                    <option value="{{ $itemsPerPage }}">
                        {{ $itemsPerPage != 0 ? $itemsPerPage : 'custom' }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
</div>

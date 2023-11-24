<div class="d-flex justify-content-between">
    <nav aria-label="Page navigation example">
        @if ($pagesTotal > 1)
            <ul class="pagination">
                @php($pagesIndex = max(0, min($pagesIndex, $pagesTotal)))
                @php($startPage = max(0, $pagesIndex - intval(7 / 2)))
                @php($endPage = min($pagesTotal, $startPage + 7 - 1))
                @if ($pagesIndex > 0)
                    <li class="page-item"><a class="page-link" wire:click.prevent="$set('pagesIndex', {{ $pagesIndex - 1 }})">Previous</a>
                    </li>
                @endif
                @for ($i = $startPage; $i < $endPage; $i++)
                    <li class="page-item"><a
                           class="page-link @if ($i == $pagesIndex) active @endif" @if ($i != $pagesIndex) wire:click.prevent="$set('pagesIndex', {{ $i }})" @endif>{{ $i }}</a>
                    </li>
                @endfor
                @if ($pagesIndex < $pagesTotal - 1)
                    <li class="page-item"><a class="page-link" wire:click.prevent="$set('pagesIndex', {{ $pagesIndex + 1 }})">Next</a></li>
                @endif
            </ul>
        @endif
    </nav>
    <div>
        <select class="form-select" wire:model="itemsPerPage">
            @foreach ([10, 20, 50, 100, 1000] as $itemsPerPage)
                <option value="{{ $itemsPerPage }}">{{ $itemsPerPage != 0 ? $itemsPerPage : 'custom' }}
                </option>
            @endforeach
        </select>
    </div>
</div>

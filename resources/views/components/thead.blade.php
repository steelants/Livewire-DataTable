<thead class="position-sticky top-0">
    <tr>
        <x-datatable-selection-header :selectable="$selectable" :select-page="$selectPage" :partially-selected="$partiallySelected" />
        @foreach ($headers as $key => $header)
            {{-- Don't rely on the headers array — it can differ entirely from the property used for sorting via a function --}}
            <th scope="col" class="text-nowrap">
                <span
                    @if ($sortable && in_array($key, $sortableColumns)) class="datatable-head-sort"
                        @if ($key != $sortBy)
                            wire:click="$set('sortBy','{{ $key }}')"
                        @else
                            wire:click="$set('sortDirection','{{ $sortDirection == 'desc' ? 'asc' : 'desc' }}')" @endif
                    @endif
                    >
                    <span>{{ ucfirst($header) }}</span>

                    @if ($sortable && in_array($key, $sortableColumns))
                        @if ($key != $sortBy)
                            <i class="fas fa-sort opacity-50"></i>
                        @else
                            @if ($sortDirection == 'asc')
                                <i class="fas fa-sort-up"></i>
                            @else
                                <i class="fas fa-sort-down"></i>
                            @endif
                        @endif
                    @endif
                </span>
            </th>
        @endforeach

        @if (method_exists($this, 'actions'))
            <th class="text-end"></th>
        @endif
    </tr>
    <x-datatable-header-filters :$headers :$headerFilters :$selectable :has-actions="method_exists($this, 'actions')" />
</thead>

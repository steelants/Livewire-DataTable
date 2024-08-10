<thead>
    <tr>
        @foreach ($headers as $key => $header)
            {{-- Nespoléhat se na proměnou headers může být uplně jiná než property sortovat přes funkci --}}
            <th scope="col">
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

    @if (!empty($headerFilters))
        <tr>
            @foreach ($headers as $key => $header)
                <td>
                    @if (isset($headerFilters[$key]))
                        <x-form::input type="{{ $headerFilters[$key]['type'] }}" wire:model.change="headerFilter.{{ $key }}" />
                    @endif
                </td>
            @endforeach

            @if (method_exists($this, 'actions'))
                <td></td>
            @endif
        </tr>
    @endif
</thead>

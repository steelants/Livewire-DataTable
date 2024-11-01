<thead>
    <tr>
        @foreach ($headers as $key => $header)
            {{-- Nespoléhat se na proměnou headers může být uplně jiná než property sortovat přes funkci --}}
            <th scope="col" calass="text-nowrap">
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
                        @if ($headerFilters[$key]['type'] == "select")
                            <select class="form-select" wire:model.change="headerFilter.{{ $key }}">
                                <option value="">{{ __('All') }}</option>
                                @if (!empty($headerFilters[$key]['values']))
                                    @foreach($headerFilters[$key]['values'] as $value => $name)
                                        <option value="{{ $value }}">{{ $name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        @elseif($headerFilters[$key]['type'] == "date" || $headerFilters[$key]['type'] == "time" || $headerFilters[$key]['type'] == "datetime-local")
                            <div class="input-group">
                                <input class="form-control" type="{{ $headerFilters[$key]['type'] }}" wire:model.change="headerFilter.{{ $key }}.from" />
                                <input class="form-control" type="{{ $headerFilters[$key]['type'] }}" wire:model.change="headerFilter.{{ $key }}.to" />
                            </div>
                        @else
                            <input class="form-control" type="{{ $headerFilters[$key]['type'] }}" wire:model.change="headerFilter.{{ $key }}" />
                        @endif
                    @endif
                </td>
            @endforeach

            @if (method_exists($this, 'actions'))
                <td></td>
            @endif
        </tr>
    @endif
</thead>

<thead>
    <tr>
        @foreach ($headers as $header)
            {{-- Nespoléhat se na proměnou headers může být uplně jiná než property sortovat přes funkci --}}
            <th @if ($sortable) @if ($header != $sortBy) wire:click="$set('sortBy','{{ $header }}')" @else wire:click="$set('sortDesc','{{ !$sortDesc }}')" @endif @endif scope="col">
                @if ($sortable)
                    @if ($header != $sortBy)
                        ↕
                    @else
                        @if ($sortDesc)
                            ↑
                        @else
                            ↓
                        @endif
                    @endif
                @endif
                {{ ucwords($header) }}
            </th>
        @endforeach
        {{-- @if (method_exists($this, 'actions'))
            <th>
                {{ __('datatable::ui.actions') }}
            </th>
        @endif --}}
    </tr>
</thead>

<thead>
    <tr>
        @foreach ($headers as $header)
            {{-- Nespoléhat se na proměnou headers může být uplně jiná než property sortovat přes funkci --}}
            <th scope="col">
                <span 
                    @if ($sortable) 
                        class="datatable-head-sort"
                        @if ($header != $sortBy) 
                            wire:click="$set('sortBy','{{ $header }}')" 
                        @else 
                            wire:click="$set('sortDirection','{{ $sortDirection == 'desc' ? 'asc' : 'desc' }}')" 
                        @endif 
                    @endif
                >
                    {{ ucwords($header) }}
                    @if ($sortable)
                        @if ($header != $sortBy)
                            <i class="fas fa-sort opacity-50"></i>
                        @else
                            @if ($sortDirection == 'asc')
                                <i class="fas fa-sort-up opacity-50"></i>
                            @else
                                <i class="fas fa-sort-down opacity-50"></i>
                            @endif
                        @endif
                    @endif
                </span>
            </th>
        @endforeach

        @if (method_exists($this, 'actions'))
            <th class="text-end">Actions</th>
        @endif
    </tr>
</thead>

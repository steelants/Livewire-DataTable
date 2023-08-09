<div>
    @if ($dataset != null)
        <table class="table">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        {{-- Nespoléhat se na proměnou headers může být uplně jiná než property sortovat přes funkci --}}
                        <th @if ($header != $sortBy) wire:click="$set('sortBy','{{ $header }}')" @else wire:click="$set('sortDesc','{{ !$sortDesc }}')" @endif scope="col">
                            @if ($header != $sortBy)
                                ↕
                            @else
                                @if ($sortDesc)
                                    ↑
                                @else
                                    ↓
                                @endif
                            @endif
                            {{ ucwords($header) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($dataset as $row)
                    <tr>
                        @foreach ($row as $key => $collum)
                            <td>{{ $collum }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            @if (!empty($footers))
                <tfoot>
                    @foreach ($footers as $footer)
                        <th scope="col">{{ $footer }}</th>
                    @endforeach
                </tfoot>
            @endif
        </table>
    @else
        <p>{{ __('Nebyly nalezeny data') }}</p>
    @endif
</div>

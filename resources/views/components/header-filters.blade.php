@if (!empty($headerFilters))
    <tr>
        @if ($selectable)
            <td></td>
        @endif
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
                    @elseif($headerFilters[$key]['type'] == "multiselect")
                        <select multiple class="form-select form-select-sm" wire:model.change="{{ $wireModel($key) }}">
                            @foreach($headerFilters[$key]['values'] ?? [] as $value => $name)
                                <option value="{{ $value }}">{{ $name }}</option>
                            @endforeach
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

        @if ($hasActions)
            <td></td>
        @endif
    </tr>
@endif

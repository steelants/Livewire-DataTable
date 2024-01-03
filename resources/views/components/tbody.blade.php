<tbody>
    @foreach ($dataset as $idx => $row)
        <tr>
            @foreach ($row as $key => $collum)
                {{-- @if (count($row) - 1 == count($headers) && $key == 'id')
                    @continue
                @endif --}}
                <td>{!! $collum !!}</td>
            @endforeach

            @if (!empty($actions))
                <td>
                    @if (isset($actions[$idx]))
                        @foreach ($actions[$idx] as $action)
                            @if ($action['type'] == 'route')
                                <a class="btn btn-secondary btn-sm" href="{{ route($action['name'], $action['parameters']) }}">
                                    {{ __($action['name']) }}</a>
                            @elseif ($action['type'] == 'livewire')
                                <button class="btn btn-secondary btn-sm" wire:click='{{ $action['action'] }}({{ $action['parameters'] }})'>
                                    {{ __($action['name']) }}</button>
                            @else
                                {{ __('datatable::ui.actions.not_implemented') }}
                            @endif
                        @endforeach
                    @endif
                </td>
            @endif
        </tr>
    @endforeach
</tbody>

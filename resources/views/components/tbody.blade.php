<tbody>
    @foreach ($dataset as $idx => $row)
        <tr>
            @if (method_exists($this, 'renderRow'))
                @php($row = $this->renderRow($row))

                @foreach (array_keys($headers) as $key)
                    <td>{!! $row[$key] ?? '' !!}</td>
                @endforeach
            @else
                @foreach (array_keys($headers) as $key)
                    @php($method = 'renderColumn' . ucfirst(Str::camel($key)))
                    @if (method_exists($this, $method))
                        <td>{!! $this->{$method}($row[$key] ?? '', $row) !!}</td>
                    @else
                        <td>{{ $row[$key] ?? '' }}</td>
                    @endif
                @endforeach
            @endif


            @if (!empty($actions))
                <td class="text-end">
                    @if (!empty($actions[$idx]))
                        <div class="dropdown position-static">
                            <button class="datatable-dropdown-action btn btn-sq btn-sm" type="button" data-bs-toggle="dropdown" data-bs-boundary="window">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                    <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                                </svg>
                            </button>

                            <div class="dropdown-menu">
                                @foreach ($actions[$idx] as $action)
                                    @if ($action['type'] == 'url')
                                        <a class="dropdown-item {{ $action['actionClass'] ?? '' }}" href="{{ $action['url'] }}">
                                            @if (!empty($action['iconClass']))
                                                <i class="dropdown-item-icon {{ $action['iconClass'] }}"></i>
                                            @endif
                                            <span>{{ __($action['text']) }}</span>
                                        </a>
                                    @elseif ($action['type'] == 'livewire')
                                        <button class="dropdown-item {{ $action['actionClass'] ?? '' }}" 
                                            wire:click='{{ $action['action'] }}({{ json_encode($action['parameters']) }})'
                                            @if(!empty($action['confirm'])) wire:confirm="{{__($action['confirm'])}}" @endif
                                        >
                                            @if (!empty($action['iconClass']))
                                                <i class="dropdown-item-icon {{ $action['iconClass'] }}"></i>
                                            @endif
                                            <span>{{ __($action['text']) }}</span>
                                        </button>
                                    @else
                                        {{ __('datatable::ui.actions.not_implemented') }}
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </td>
            @endif
        </tr>
    @endforeach
</tbody>

<div>
    @if ($dataset != null)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            {{-- Nespoléhat se na proměnou headers může být uplně jiná než property sortovat přes funkci --}}
                            <th @if($sortable) @if ($header != $sortBy) wire:click="$set('sortBy','{{ $header }}')" @else wire:click="$set('sortDesc','{{ !$sortDesc }}')" @endif  @endif scope="col">
                                @if($sortable)
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
                        @if (method_exists($this, 'actions'))
                            <th>
                                {{ __('datatable::ui.actions') }}
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataset as $row)
                        <tr>
                            @foreach ($row as $key => $collum)
                                <td>{{ $collum }}</td>
                            @endforeach
                            @if (method_exists($this, 'actions'))
                                <td>
                                    @foreach ($this->actions($row) as $action)
                                        @if ($action['type'] == 'route')
                                            <a href="{{ route($action['name'], $action['parameters']) }}"> {{ __($action['name']) }}</a>
                                        @elseif ($action['type'] == 'livewire')
                                            <button wire:click='{{ $action['action'] }}({{ $action['parameters'] }})'> {{ __($action['name']) }}</button>
                                        @else
                                            {{ __('datatable::ui.actions.not_implemented') }}
                                        @endif
                                    @endforeach
                                </td>
                            @endif
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
        </div>
        @if ($paginated == true)
            <div class="d-flex justify-content-between">
                <nav aria-label="Page navigation example">
                    @if ($pagesTotal > 1)
                        <ul class="pagination">
                            @php($pagesIndex = max(0, min($pagesIndex, $pagesTotal)))
                            @php($startPage = max(0, $pagesIndex - intval(7 / 2)))
                            @php($endPage = min($pagesTotal, $startPage + 7 - 1))
                            @if ($pagesIndex > 0)
                                <li class="page-item"><a class="page-link" wire:click.prevent="$set('pagesIndex', {{ $pagesIndex - 1 }})">Previous</a></li>
                            @endif
                            @for ($i = $startPage; $i < $endPage; $i++)
                                <li class="page-item"><a @if ($i != $pagesIndex) wire:click.prevent="$set('pagesIndex', {{ $i }})" @endif class="page-link @if ($i == $pagesIndex) active @endif">{{ $i }}</a></li>
                            @endfor
                            @if ($pagesIndex < ($pagesTotal -1 ))
                                <li class="page-item"><a class="page-link" wire:click.prevent="$set('pagesIndex', {{ $pagesIndex + 1 }})">Next</a></li>
                            @endif
                        </ul>
                    @endif
                </nav>
                <div>
                    <select class="form-select" wire:model="itemsPerPage">
                        @foreach ([10, 20, 50, 100, 1000] as $itemsPerPage)
                            <option value="{{ $itemsPerPage }}">{{ $itemsPerPage != 0 ? $itemsPerPage : 'custom' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    @else
        <p>{{ __('datatable::ui.nothing_found') }}</p>
    @endif
</div>

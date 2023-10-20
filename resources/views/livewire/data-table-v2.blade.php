<div>
    @if ($dataset != null)
        <div class="table-responsive">
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
        @if ($pagesTotal > 1)
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    @if ($pagesIndex > 0)
                        <li class="page-item"><a class="page-link" wire:click.prevent="$set('pagesIndex', {{ $pagesIndex - 1 }})">Previous</a></li>
                    @endif
                    @for ($pageNumber = 0; $pageNumber <= $pagesTotal; $pageNumber++)
                        @if ($pageNumber > $pagesIndex + 3 || $pageNumber < $pagesIndex - 3)
                            @continue
                        @endif
                        <li class="page-item"><a class="page-link @if ($pagesIndex == $pageNumber) active @endif" wire:click.prevent="$set('pagesIndex', {{ $pageNumber }})">{{ $pageNumber +1 }}</a></li>
                    @endfor
                    @if ($pagesIndex < $pagesTotal)
                        <li class="page-item"><a class="page-link" wire:click.prevent="$set('pagesIndex', {{ $pagesIndex + 1 }})">Next</a></li>
                    @endif
                </ul>
            </nav>
        @endif
    @else
        <p>{{ __('datatable::ui.nothing_found') }}</p>
    @endif
</div>

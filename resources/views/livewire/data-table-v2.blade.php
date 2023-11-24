<div>
    @if ($dataset != null)
        <div class="table-responsive">
            <table class="table">
                <x-datatable-head :headers=$headers :sortable={{$sortable}} :sortBy='{{$sortBy}}' :sortDesc={{$sortDesc}}/>
                <tbody>
                    @foreach ($dataset as $row)
                        <tr>
                            @foreach ($row as $key => $collum)
                                @if (count($row) - 1 == count($headers) && $key == 'id')
                                    @continue
                                @endif
                                <td>{{ $collum }}</td>
                            @endforeach
                            @if (method_exists($this, 'actions'))
                                <td>
                                    @foreach ($this->actions($row) as $action)
                                        @if ($action['type'] == 'route')
                                            <a href="{{ route($action['name'], $action['parameters']) }}">
                                                {{ __($action['name']) }}</a>
                                        @elseif ($action['type'] == 'livewire')
                                            <button wire:click='{{ $action['action'] }}({{ $action['parameters'] }})'>
                                                {{ __($action['name']) }}</button>
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
                    <x-datatable-foot :footers='{{ $footers }}' />
                @endif
            </table>
        </div>
        @if ($paginated == true)
            <x-datatable-pagination :pagesIndex='{{ $pagesIndex }}' :itemsPerPage='{{ $itemsPerPage }}' :pagesTotal='{{ $pagesTotal }}' />
        @endif
    @else
        <p>{{ __('datatable::ui.nothing_found') }}</p>
    @endif
</div>

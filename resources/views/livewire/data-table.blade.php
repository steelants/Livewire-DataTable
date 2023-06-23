<div>
    @if ($colum_to_search != [])
        <form class="d-flex" role="search" wire:submit.prevent="searchString()">
            @csrf
            <input aria-label="Search" class="form-control me-2" placeholder="Search" type="search" wire:model=search_string>
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
    @endif
    @if ($dataGetFromDB != null)
        <div class="table-responsive">
            <table class="table table-mobile-break">
                <caption>
                    @if ($total_pages > 1)
                        page {{ $actual_page }} of {{ $total_pages }} pages
                    @endif
                </caption>
                <thead>
                    <tr>
                        @foreach ($headers as $key => $header)
                            <th scope="col" wire:click={{ strpos($properties2[count($properties2) - count($headers) + $key], '->') === false ? 'orderBy' : 'sortBy' }}('{{ str_replace('->', '.', $properties2[count($properties2) - count($headers) + $key]) }}')>
                                {{ ucwords($header) }}
                                @if (($order_by != null && $order_by == $properties2[count($properties2) - count($headers) + $key]) || ($sort_by != null && $sort_by == str_replace('->', '.', $properties2[count($properties2) - count($headers) + $key])))
                                    @if ($order_direction != 'asc')
                                        <i>↑</i>
                                    @else($order_direction != 'asc')
                                        <i>↓</i>
                                    @endif
                                @else
                                    <i>↕</i>
                                @endif
                            </th>
                        @endforeach
                        <th class="text-center">{{ __('Akce') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataGetFromDB as $item)
                        <tr>
                            @foreach ($headers as $key => $header)
                                <td class="text-truncate">
                                    @if (self::getTotals() != [] && end($dataGetFromDB) == $item)
                                        <b>
                                    @endif
                                    {{ $item[$properties2[count($properties2) - count($headers) + $key]] }}
                                    @if (end($dataGetFromDB) == $item)
                                        </b>
                                    @endif
                                </td>
                            @endforeach
                            @if (self::getActions($item) != null && (self::getTotals() == [] || end($dataGetFromDB) != $item))
                                <td>
                                    <div class="d-flex justify-content-end align-items-center">
                                        @foreach (self::getActions($item) as $action)
                                            <a @if ($action['is_danger'] ?? false) onclick="return confirm('datatables.action.configmation')" @endif class="btn ms-1 @if ($action['is_danger'] ?? false) btn-danger @else btn-secondary @endif" href="{{ isset($action['route']) ? route($action['route']['name'], (array) ($action['route']['parameters'] ? [$action['route']['parameters'][0] => $item[$action['route']['parameters'][1]]] : [])) : '' }}" title="{{ __($action['lang_title']) }}">
                                                <div class="d-inline d-md-inline">{{ __($action['lang_title']) }}</div>
                                                <div class="d-none d-md-none">
                                                    <i class="fa {{ $action['icon'] }}"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                            @else
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($total_pages > 1)
            <div class="d-flex justify-content-between">
                <div>
                    <napravo>
                        <ul class="pagination">
                            @if ($actual_page > 0)
                                <li class="page-item">
                                    <a class="page-link" wire:click="previousPage()">
                                        PREVIOUS
                                    </a>
                                </li>
                            @endif
                            @for ($page_index = $actual_page - 4; $page_index <= $actual_page + 4; $page_index++)
                                @if ($page_index < 0 || $page_index > $total_pages)
                                    @continue
                                @endif
                                <li class="page-item">
                                    <a class="page-link @if ($page_index == $actual_page) active @endif" wire:click="setPage({{ $page_index }})">
                                        {{ $page_index }}
                                    </a>
                                </li>
                            @endfor
                            @if ($actual_page < $total_pages)
                                <li class="page-item">
                                    <a class="page-link" wire:click="nextPage()">
                                        NEXT
                                    </a>
                                </li>
                            @endif
                        </ul>
                        </nav>
                </div>
                <div>
                    <select class="form-select" wire:model="items_per_page">
                        @foreach ([10, 20, 50, 100, 1000, 0] as $limit)
                            <option value="{{ $limit }}">{{ $limit != 0 ? $limit : 'all' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    @else
        <p>{{ __('Nebyly nalezeny data') }}</p>
    @endif
</div>

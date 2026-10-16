{{--
    Livewire vkládá kolem každého @if/@foreach dva morph markery (HTML komentáře). Ve smyčce
    přes řádky a sloupce jich vznikaly desítky na řádek, přitom podmínky (renderRow, render
    casty, renderColumnX, výběr, sloupec akcí) se mezi řádky nemění. Proto se rozhodnou jednou
    pro celou tabulku a buňky se skládají v PHP - per řádek zbývají jen direktivy akcí.
--}}
@php
    // Escapování plain sloupců přesně jako {{ }}, včetně echo handlerů (Blade::stringable).
    $bladeCompiler = app('blade.compiler');
    $escape = fn ($value) => e($bladeCompiler->applyEchoHandler($value));

    if (method_exists($this, 'renderRow')) {
        // renderRow() nahrazuje casty i renderColumnX() pro celý řádek, výstup je neescapovaný.
        $renderCells = function ($row) use ($headers) {
            $rendered = $this->renderRow($row);
            $html = '';
            foreach (array_keys($headers) as $key) {
                $html .= '<td>' . Arr::get($rendered, $key) . '</td>';
            }

            return $html;
        };
    } else {
        // Pro každý sloupec jednou: render cast, renderColumnX() (obojí neescapované), jinak plain.
        $columns = [];
        foreach (array_keys($headers) as $key) {
            $method = 'renderColumn' . ucfirst(Str::camel(str_replace('.', '_', $key)));
            if (isset($renderCasts[$key])) {
                $cast = app($renderCasts[$key]);
                $columns[$key] = fn ($row) => $cast->render($key, Arr::get($row, $key), $row);
            } elseif (method_exists($this, $method)) {
                $columns[$key] = fn ($row) => $this->{$method}(Arr::get($row, $key), $row);
            } else {
                $columns[$key] = fn ($row) => $escape(Arr::get($row, $key));
            }
        }
        $renderCells = function ($row) use ($columns) {
            $html = '';
            foreach ($columns as $column) {
                $html .= '<td>' . $column($row) . '</td>';
            }

            return $html;
        };
    }

    // Buňka výběru se renderuje jen u tabulky s výběrem - jinak se komponenta vůbec nevolá.
    $renderSelection = $selectable
        ? fn ($row) => \Illuminate\Support\Facades\Blade::renderComponent(new \SteelAnts\DataTable\View\Components\SelectionCell(
            selectable: true,
            row: $row,
            keyPropery: $keyPropery,
            selected: $selected,
        ))
        : fn ($row) => '';

    // Sloupec akcí má buď celá tabulka, nebo žádný řádek.
    $hasActions = !empty($actions);
@endphp
<tbody>
    {{-- Dvě smyčky místo @if pro každý řádek: i nesplněná podmínka dostane markery. --}}
    @if ($hasActions)
        @foreach ($dataset as $idx => $row)
            <tr wire:key="row-{{ data_get($row, $keyPropery) ?? $idx }}">
                {!! $renderSelection($row) !!}
                {!! $renderCells($row) !!}
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
                                    @php($icon = !empty($action['iconClass']) ? '<i class="dropdown-item-icon ' . e($action['iconClass']) . '"></i>' : '')
                                    @if ($action['type'] == 'url')
                                        <a class="dropdown-item {{ $action['actionClass'] ?? '' }}" href="{{ $action['url'] }}" @attrs($action['attributes'] ?? null)>
                                            {!! $icon !!}
                                            <span>{{ __($action['text']) }}</span>
                                        </a>
                                    @elseif ($action['type'] == 'livewire')
                                        <button type="button" class="dropdown-item {{ $action['actionClass'] ?? '' }}"
                                            wire:click='{{ $action['action'] }}({{ json_encode($action['parameters']) }})'
                                            @if(!empty($action['confirm'])) wire:confirm="{{__($action['confirm'])}}" @endif
											@attrs($action['attributes'] ?? null)
                                        >
                                            {!! $icon !!}
                                            <span>{{ __($action['text']) }}</span>
                                        </button>
                                    @elseif ($action['type'] == 'onclick')
                                        <button type="button" class="dropdown-item {{ $action['actionClass'] ?? '' }}"
                                            onclick='{{ $action['action'] }}({{ json_encode($action['parameters']) }})'
											@attrs($action['attributes'] ?? null)
                                        >
                                            {!! $icon !!}
                                            <span>{{ __($action['text']) }}</span>
                                        </button>
                                    @else
                                        {{ __('Actions not implemented!') }}
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    @else
        @foreach ($dataset as $idx => $row)
            <tr wire:key="row-{{ data_get($row, $keyPropery) ?? $idx }}">
                {!! $renderSelection($row) !!}
                {!! $renderCells($row) !!}
            </tr>
        @endforeach
    @endif
</tbody>

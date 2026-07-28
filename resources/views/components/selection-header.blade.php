@if ($selectable)
    <th style="width: 1%">
        {{-- Akce, ne wire:model - "je stranka vybrana" je odvozeny stav, ktery
             se pocita na serveru z vyberu a klicu na aktualni strance. --}}
        <input type="checkbox" class="form-check-input"
               wire:click="toggleSelectPage"
               @checked($selectPage)
               @if ($partiallySelected) x-init="$el.indeterminate = true" @endif>
    </th>
@endif

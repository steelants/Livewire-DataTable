@if ($selectable)
    <th style="width: 1%">
        {{-- An action, not wire:model - "is the page selected" is a derived state
             computed server-side from the selection and the current page's keys.
             Klíč se mění se stavem, takže Livewire input při změně vymění. Morph jinak
             přepíše jen atribut checked, ne DOM vlastnost, kterou přepnul klik uživatele -
             checkbox by po vyprázdnění výběru (např. po bulk akci) zůstal zaškrtnutý
             a další klik by místo odznačení znovu označil celou stránku. --}}
        <input type="checkbox" class="form-check-input"
               wire:key="datatable-select-page-{{ $selectPage ? 'all' : ($partiallySelected ? 'some' : 'none') }}"
               wire:click="toggleSelectPage"
               @checked($selectPage)
               @if ($partiallySelected) x-init="$el.indeterminate = true" @endif>
    </th>
@endif

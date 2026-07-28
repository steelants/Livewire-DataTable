@if ($selectable)
    <th style="width: 1%">
        {{-- An action, not wire:model - "is the page selected" is a derived state
             computed server-side from the selection and the current page's keys. --}}
        <input type="checkbox" class="form-check-input"
               wire:click="toggleSelectPage"
               @checked($selectPage)
               @if ($partiallySelected) x-init="$el.indeterminate = true" @endif>
    </th>
@endif

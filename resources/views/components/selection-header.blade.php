@if ($selectable)
    <th style="width: 1%">
        {{-- An action, not wire:model - "is the page selected" is a derived state
             computed server-side from the selection and the current page's keys.
             The key changes with the state, so Livewire replaces the input. Otherwise morph
             only rewrites the checked attribute, not the DOM property toggled by the click -
             the checkbox would stay checked after the selection is cleared (e.g. by a bulk
             action) and the next click would select the whole page again. --}}
        <input type="checkbox" class="form-check-input"
               wire:key="datatable-select-page-{{ $selectPage ? 'all' : ($partiallySelected ? 'some' : 'none') }}"
               wire:click="toggleSelectPage"
               @checked($selectPage)
               @if ($partiallySelected) x-init="$el.indeterminate = true" @endif>
    </th>
@endif

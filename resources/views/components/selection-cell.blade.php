@if ($selectable)
    <td>
        <input type="checkbox" class="form-check-input" wire:model.live="selected"
               value="{{ \Illuminate\Support\Arr::get($row, $keyPropery) }}">
    </td>
@endif

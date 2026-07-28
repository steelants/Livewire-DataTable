@if ($selectable)
    <td>
        <input type="checkbox" class="form-check-input" wire:model.live="selected"
               value="{{ $rowKey }}">
    </td>
@endif

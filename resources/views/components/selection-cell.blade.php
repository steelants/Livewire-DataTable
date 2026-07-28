@if ($selectable)
    <td class="datatable-selection-cell">
        @if ($rowKey !== null)
            <input type="checkbox" class="form-check-input" wire:model.live="selected"
                   value="{{ $rowKey }}" @checked($isSelected)>
        @endif
    </td>
@endif

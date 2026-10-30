@if ($selectable)
    <td class="datatable-selection-cell">
        @if ($rowKey !== null)
            <input type="checkbox" class="check" wire:model.live="selected"
                   value="{{ $rowKey }}" @checked($isSelected)>
        @endif
    </td>
@endif

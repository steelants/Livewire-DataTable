@if ($visible())
    <div class="datatable-bulk-bar d-flex align-items-center flex-wrap gap-3 p-2 mb-3 bg-body-tertiary rounded-2">
        <span class="fw-semibold text-nowrap">
            {{ __(':count of :total selected', ['count' => count($selected), 'total' => $itemsTotal]) }}
        </span>

        <div class="d-flex flex-wrap gap-2">
            @foreach ($bulkActions as $action)
                @if (($action['type'] ?? 'livewire') === 'url')
                    <a class="btn btn-sm {{ $action['actionClass'] ?? 'btn-outline-secondary' }}" href="{{ $action['url'] }}">
                        @if (!empty($action['iconClass']))
                            <i class="{{ $action['iconClass'] }} me-1"></i>
                        @endif
                        <span>{{ __($action['text']) }}</span>
                    </a>
                @else
                    {{-- Dispatch by action name, not by index: bulkActions() can be
                         permission-conditioned and indexes then shift. --}}
                    <button type="button" class="btn btn-sm {{ $action['actionClass'] ?? 'btn-outline-secondary' }}"
                            wire:click="callBulkAction('{{ $action['action'] }}')"
                            wire:loading.attr="disabled"
                            @if (!empty($action['confirm'])) wire:confirm="{{ __($action['confirm']) }}" @endif>
                        @if (!empty($action['iconClass']))
                            <i class="{{ $action['iconClass'] }} me-1"></i>
                        @endif
                        <span wire:loading.remove wire:target="callBulkAction">{{ __($action['text']) }}</span>
                        <span wire:loading wire:target="callBulkAction">
                            <i class="fas fa-spinner fa-spin me-1"></i>{{ __('Processing...') }}
                        </span>
                    </button>
                @endif
            @endforeach
        </div>

        <div class="d-flex flex-wrap gap-2 ms-auto">
            @if ($canSelectAll())
                <button type="button" class="btn btn-sm btn-link" wire:click="selectAllFiltered">
                    {{ __('Select all :total', ['total' => $itemsTotal]) }}
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-link" wire:click="clearSelection">
                {{ __('Clear selection') }}
            </button>
        </div>
    </div>
@endif

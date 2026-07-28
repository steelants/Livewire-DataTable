<div class="row g-2 mb-3">
    @if ($searchable)
        <div class="col-md-6 col-lg-3 me-auto">
            <input type="text" class="form-control" placeholder="{{ __('Search...') }}" wire:model.live.debounce="searchValue">
        </div>
        {{-- <div class="col-auto">
            <button type="button" class="btn btn-outline" data-bs-toggle="collapse" data-bs-target="#filters_{{ $id }}">
                <i class="me-2 fas fa-filter"></i>
                <span>{{ __('datatable::ui.filters') }}</span>
                <span class="badge text-bg-secondary ms-2">3</span>
            </button>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-outline">
                <i class="me-2 fas fa-download"></i>
                <span>{{ __('datatable::ui.export') }}</span>
            </button>
        </div> --}}
    @endif
    @if (!empty($filename))
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-light" wire:click="serv()">
                <span>{{ __('Export') }}</span>
            </button>
        </div>
    @endif
</div>

<div class="collapse" id="filters_{{ $id }}">
    <div class="card mb-3">
        <div class="card-body">
            <div class="row gx-2 gy-3 mb-3">
                <div class="col-md-4 col-lg-3">

                </div>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <button type="button" class="btn btn-primary">
                    <span>{{ __('Filter actin') }}</span>
                </button>
                <button type="button" class="btn btn-outline">
                    <span>{{ __('Reset filter') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div>
    @if($searchable)
        <div class="row mb-3">
            <div class="col-md-6 col-lg-4">
                <input type="text" class="form-control" placeholder="Search..." wire:model="searchValue">
            </div>
        </div>
    @endif

    @if ($dataset != null)
        <div class="table-responsive">
            <table class="{{ $tableClass }}">

                @if($showHeader)
                    <x-datatable-head :headers="$headers" :sortable="$sortable" :sortBy="$sortBy" :sortDirection="$sortDirection"/>
                @endif
                
                <x-datatable-body :dataset="$dataset" :actions="$actions" :headers="$headers" />
                
                @if (!empty($footers))
                    <x-datatable-foot :footers="$footers" />
                @endif
            </table>
        </div>
        @if ($paginated == true)
            <x-datatable-pagination :currentPage="$currentPage" :itemsPerPage="$itemsPerPage" :pagesTotal="$pagesTotal" :itemsTotal="$itemsTotal"/>
        @endif
    @else
        <div class="text-center p-5 bg-body-secondary opacity-50">{{ __('datatable::ui.nothing_found') }}</div>
    @endif
</div>

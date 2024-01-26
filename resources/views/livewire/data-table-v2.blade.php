<div>
    @if ($dataset != null)
        <div class="table-responsive">
            <table class="{{ $tableClass }}">
                <x-datatable-head :headers="$headers" :sortable="$sortable" :sortBy="$sortBy" :sortDirection="$sortDirection"/>
                
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
        <p>{{ __('datatable::ui.nothing_found') }}</p>
    @endif
</div>

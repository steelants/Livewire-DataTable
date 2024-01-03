<div>
    @if ($dataset != null)
        @dump($paginated)
        @dump($currentPage)
        @dump($pagesTotal)
        @dump($itemsTotal)
        @dump($itemsPerPage)

        <div class="table-responsive">
            <table class="table">
                <x-datatable-head :headers="$headers" :sortable="$sortable" :sortBy="$sortBy" :sortDesc="$sortDesc"/>
                
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

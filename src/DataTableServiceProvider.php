<?php

namespace SteelAnts\DataTable;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use SteelAnts\DataTable\Http\Livewire\DataTable;
use SteelAnts\DataTable\Http\Livewire\GenericDataTable;
use SteelAnts\DataTable\Console\Commands\CreateDataTableCommand;
use SteelAnts\DataTable\Http\Livewire\DataTableV2;
use SteelAnts\DataTable\View\Components\Pagination;
use SteelAnts\DataTable\View\Components\Head;
use SteelAnts\DataTable\View\Components\Body;
use SteelAnts\DataTable\View\Components\Foot;

class DataTableServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'datatable');

        $this->loadViewsFrom(__DIR__ . '/../resources/views/components', 'datatable-components');
        Blade::component('datatable-pagination', Pagination::class);
        Blade::component('datatable-foot', Foot::class);
        Blade::component('datatable-head', Head::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views/livewire', 'datatable');
        Livewire::component('datatable', DataTable::class);
        Livewire::component('generic-data-table', GenericDataTable::class);
        Livewire::component('datatable-v2', DataTableV2::class);

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/datatable'),
            __DIR__ . '/../resources/views/views/components' => resource_path('views/vendor/datatable/components'),
            __DIR__ . '/../resources/views/livewire/' => resource_path('views/vendor/datatable'),
            __DIR__.'/../lang' => $this->app->langPath('vendor/datatable'),
        ]);

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([CreateDataTableCommand::class,]);
    }

    public function register()
    {
    }
}

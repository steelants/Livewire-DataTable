<?php
namespace SteelAnts\DataTable;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Livewire;
use SteelAnts\DataTable\Http\Livewire\DataTable;
use SteelAnts\DataTable\Http\Livewire\GenericDataTable;


class DataTableServiceProvider extends ServiceProvider {

    public function boot(){
        Livewire::component('datatable', DataTable::class);
        Livewire::component('generic-data-table', GenericDataTable::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views/livewire', 'datatable');
    }

    public function register(){

    }
}

# LV-DataTable
### Created by: [SteelAnts s.r.o.](https://www.steelants.cz/)

[![Total Downloads](https://img.shields.io/packagist/dt/steelants/datatable.svg?style=flat-square)](https://packagist.org/packages/steelants/datatable)

## Examples
Use of Generic Prefab:
```html
    @livewire('generic-data-table', [
        'model' => 'WorkTime',
        'properties' => ['id', 'day', 'data'],
        'headers' => [__('Den'), __('Doba')],
        'items_per_page' => 20,
        'colum_to_search' => [],
        'actions' => [
            [
                'icon' => '',
                'lang_title' => 'Upravit',
                #'is_danger' => True,
                'route' => [
                    'name' => 'work.time.edit',
                    'parameters' => ['workTime', 'id'],
                ],
            ],
            [
                'icon' => '',
                'lang_title' => 'Smazat',
                'is_danger' => True,
                'route' => [
                    'name' => 'work.time.delete',
                    'parameters' => ['workTime', 'id'],
                ],
            ],
        ],
    ])
```

Use of Use Of Custom Data Table:
```php
namespace App\Http\Livewire\Components;

use Livewire\Component;
use SteelAnts\DataTable\Http\Livewire\DataTable;

class TaskDataTable extends DataTable
{
    public function mount()
    {
        $this->reset();
        parent::setModel("Task");
        parent::setHeaders([__('Uživatel'), __('Popisek')]);
        parent::setItemPerPage(20);
        parent::setProperties(['id', 'user.name', 'description']);
        parent::setSearchColumns(['description']);

        parent::addAction( '', 'Upravit', [
            'name' => 'task.edit',
            'parameters' => ['task_id', 'id'],
        ], false);

        parent::setWhere("contract_id", "=", 1);
        parent::getData();
    }

    public function rowMutator($item) : array{
        $item["description"] = \Str::limit($item['description'], 60);
        return $item;
    }
}
```
```html
    @livewire('components.task-data-table', [], key($contract->id))
```
```shell
git tag x.x.x
git push --tags
```
s
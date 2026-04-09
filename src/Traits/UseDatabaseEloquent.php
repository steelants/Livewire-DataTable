<?php

namespace SteelAnts\DataTable\Traits;

use SteelAnts\DataTable\Traits\UseDatabase;

trait UseDatabaseEloquent
{
    use UseDatabase;

    protected function buildRow($item, array $columnMethodCache, array $columnPropertyCache): mixed
    {
        return $item;
    }
}

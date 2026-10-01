<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\RenderCasts\RenderCast;

class BoolCast implements RenderCast
{
    public function render($key, $value, $model)
    {
        return '<span class="cast-' . $key . '-' . ($value ? 'yes' : 'no') . '">#' . e($model['id']) . '</span>';
    }
}

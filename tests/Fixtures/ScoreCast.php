<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\RenderCasts\RenderCast;

class ScoreCast implements RenderCast
{
    public function render($key, $value, $model)
    {
        return '<span class="score" data-row="' . e($model['id']) . '">' . e($value) . ' b.</span>';
    }
}

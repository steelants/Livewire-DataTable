<?php

namespace SteelAnts\DataTable\RenderCasts;

interface RenderCast
{
    public function render($key, $value, $model);
}

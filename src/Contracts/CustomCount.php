<?php

namespace LaravelEnso\Tables\Contracts;

use LaravelEnso\Helpers\Services\Obj;

interface CustomCount
{
    public function count(Obj $params): int;
}

<?php

namespace LaravelEnso\Tables\app\Traits;

trait Init
{
    public function __invoke()
    {
        return (new $this->tableClass())->init();
    }
}

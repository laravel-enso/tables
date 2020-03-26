<?php

namespace LaravelEnso\Tables\App\Contracts;

interface CustomCountCacheKey
{
    public function countCacheKey(): string;
}

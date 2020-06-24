<?php

namespace LaravelEnso\Tables\Contracts;

interface CustomCountCacheKey
{
    public function countCacheKey(): string;
}

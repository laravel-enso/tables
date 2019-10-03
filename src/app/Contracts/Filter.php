<?php

namespace LaravelEnso\Tables\app\Contracts;

interface Filter
{
    public function handle(): bool;
}

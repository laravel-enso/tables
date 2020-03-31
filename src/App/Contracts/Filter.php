<?php

namespace LaravelEnso\Tables\App\Contracts;

interface Filter
{
    public function applies(): bool;

    public function handle();
}

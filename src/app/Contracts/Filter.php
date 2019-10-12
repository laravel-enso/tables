<?php

namespace LaravelEnso\Tables\app\Contracts;

interface Filter
{
    public function applies(): bool;

    public function handle();
}

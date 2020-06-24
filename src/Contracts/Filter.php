<?php

namespace LaravelEnso\Tables\Contracts;

interface Filter
{
    public function applies(): bool;

    public function handle();
}

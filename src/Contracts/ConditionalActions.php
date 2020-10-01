<?php

namespace LaravelEnso\Tables\Contracts;

interface ConditionalActions
{
    public function render(array $row, string $action): bool;
}

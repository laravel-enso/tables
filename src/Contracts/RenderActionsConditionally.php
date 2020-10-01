<?php

namespace LaravelEnso\Tables\Contracts;

interface RenderActionsConditionally
{
    public function render(array $row, string $action): bool;
}

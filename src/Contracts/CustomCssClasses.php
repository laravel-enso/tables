<?php

namespace LaravelEnso\Tables\Contracts;

interface CustomCssClasses
{
    public function cssClasses(array $row): array;
}

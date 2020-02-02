<?php

namespace LaravelEnso\Tables\App\Contracts;

interface ComputesArrayColumns extends ComputesColumns
{
    public static function handle(array $row): array;
}

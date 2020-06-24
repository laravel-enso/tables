<?php

namespace LaravelEnso\Tables\Contracts;

interface ComputesArrayColumns extends ComputesColumns
{
    public static function handle(array $row): array;
}

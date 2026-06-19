<?php

namespace LaravelEnso\Tables\Contracts;

interface CustomExportChunk
{
    public function exportChunk(): int;
}

<?php

namespace LaravelEnso\Tables\Services\Data;

use LaravelEnso\Tables\Contracts\ComputesModelColumns;
use LaravelEnso\Tables\Exceptions\ModelComputor;
use LaravelEnso\Tables\Services\Data\Computors\Resource;

class ModelComputors extends Computors
{
    protected static array $computors = [
        'resource' => Resource::class,
    ];

    protected static function computor($computor): ComputesModelColumns
    {
        $computor = new self::$computors[$computor]();

        if (! $computor instanceof ComputesModelColumns) {
            throw ModelComputor::missingInterface();
        }

        return $computor;
    }
}

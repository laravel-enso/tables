<?php

namespace LaravelEnso\Tables\App\Services\Data;

use LaravelEnso\Tables\App\Contracts\ComputesModelColumns;
use LaravelEnso\Tables\App\Exceptions\ModelComputor;
use LaravelEnso\Tables\App\Services\Data\Computors\Resource;

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

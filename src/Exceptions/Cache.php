<?php

namespace LaravelEnso\Tables\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;

class Cache extends EnsoException
{
    public static function missingTrait(string $model)
    {
        return new static(__(
            'To cache the table count model :model must use the "TableCache" trait',
            ['model' => $model]
        ));
    }
}

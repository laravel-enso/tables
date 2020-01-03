<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class Cache extends EnsoException
{
    public static function missingTrait($model)
    {
        return new static(__(
            'To cache the table count model :model must use the "TableCache" trait',
            ['model' => $model]
        ));
    }
}

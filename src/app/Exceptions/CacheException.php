<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class CacheException extends EnsoException
{
    public static function missingTrait($model)
    {
        return new static(__(
            'To cache the table count model :model must use the "TableCache" trait',
            ['model' => $model]
        ));
    }
}

<?php

namespace LaravelEnso\Tables\app\Services\Table;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Computors\Date;
use LaravelEnso\Tables\app\Services\Table\Computors\Enum;
use LaravelEnso\Tables\app\Services\Table\Computors\Cents;
use LaravelEnso\Tables\app\Services\Table\Computors\Translator;

class Computors
{
    private static $fetchMode = false;

    private static $computors = [
        'enum' => Enum::class,
        'cents' => Cents::class,
        'date' => Date::class,
        'translatable' => Translator::class,
    ];

    public static function handle(Template $template, Collection $data)
    {
        self::columns($template);

        $data = self::computors($template)
            ->reduce(function ($data, $meta) {
                return $data->map(function ($row) use ($meta) {
                    return self::$computors[$meta]::handle($row);
                });
            }, $data);

        return $data;
    }

    public static function columns(Template $template)
    {
        self::computors($template)
            ->each(function ($meta) use ($template) {
                self::$computors[$meta]::columns($template->columns());
            });
    }

    public static function fetchMode()
    {
        self::$fetchMode = true;
    }

    private static function computors(Template $template)
    {
        return $template->meta()->filter()->keys()
            ->intersect(collect(self::$computors)->keys())
            ->filter(function ($computor) use ($template) {
                return $computor !== 'translatable' || self::$fetchMode;
            });
    }
}

<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\app\Services\Table\Request;

class Computors
{
    private static $computors = [
        'enum' => Enum::class,
        'cents' => Cents::class,
        'date' => Date::class,
        'translatable' => Translatator::class,
    ];

    public static function handle(Request $request, Collection $data)
    {
        $data = self::computors($request)
            ->reduce(function ($data, $meta) {
                return $data->map(function ($row) use ($meta) {
                    return self::$computors[$meta]::handle($row);
                });
            }, $data);

        return $data;
    }

    public static function columns(Request $request)
    {
        self::computors($request)
            ->each(function ($meta) use ($request) {
                self::$computors[$meta]::columns($request->get('columns'));
            });
    }

    private static function computors(Request $request)
    {
        return $request->meta()->filter()->keys()
            ->intersect(collect(self::$computors)->keys())
            ->filter(function ($computor) use ($request) {
                return $computor !== 'translatable' || $request->fetchMode();
            });
    }
}

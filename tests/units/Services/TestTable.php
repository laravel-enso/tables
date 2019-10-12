<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;

class TestTable implements Table
{
    private static $path = __DIR__.'/templates/template.json';

    public function __construct()
    {
        self::cache('never');
    }

    public function query(): Builder
    {
        return TestModel::selectRaw('id, name, is_active, created_at, price, color');
    }

    public function templatePath(): string
    {
        return self::$path;
    }

    public static function cache($type)
    {
        $template = collect(json_decode(File::get(self::$path), true));
        $template->forget('templateCache');

        if ($type !== null) {
            $template['templateCache'] = $type;
        }

        File::put(self::$path, $template->toJson(JSON_PRETTY_PRINT));
    }
}

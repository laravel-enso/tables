<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelEnso\Tables\Contracts\Table;

class TestTable implements Table
{
    private static $path = __DIR__.'/templates/template.json';

    public function __construct()
    {
        self::cache('never');
    }

    public function query(): Builder
    {
        return TestModel::selectRaw('id, name, is_active, created_at, price');
    }

    public function templatePath(): string
    {
        return self::$path;
    }

    public static function cache($type)
    {
        $template = new Collection(json_decode(File::get(self::$path), true));
        $template->forget('templateCache');

        if ($type !== null) {
            $template['templateCache'] = $type;
        }

        File::put(self::$path, $template->toJson(JSON_PRETTY_PRINT));
    }
}

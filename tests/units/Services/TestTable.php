<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\ParallelTesting;
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
        return TestModel::selectRaw('id, name, created_at, price');
    }

    public function templatePath(): string
    {
        $key = 'table_'.ParallelTesting::token().'_template';

        return Cache::get($key, self::$path);
    }

    public static function cache($type)
    {
        $key = 'table_'.ParallelTesting::token().'_template';

        $path = Cache::get($key, self::$path);

        $template = new Collection(json_decode(File::get($path), true));
        $template->forget('templateCache');

        if ($type !== null) {
            $template['templateCache'] = $type;
        }

        File::put($path, $template->toJson(JSON_PRETTY_PRINT));
    }
}

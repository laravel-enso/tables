<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Services\TemplateLoader;

trait Init
{
    public function __invoke(Request $request)
    {
        $table = App::make($this->tableClass, [
            'config' => new Config($request->all())
        ]);

        return TemplateLoader::load($table)->handle();
    }
}

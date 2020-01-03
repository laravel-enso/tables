<?php

namespace LaravelEnso\Tables\App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\App\Services\Data\Config;
use LaravelEnso\Tables\App\Services\TemplateLoader;

trait ProvidesData
{
    use ProvidesRequest;

    public function data(Request $request)
    {
        $request = $this->request($request);
        $table = App::make($this->tableClass, ['request' => $request]);
        $template = (new TemplateLoader($table))->handle();
        $config = new Config($request, $template);

        return ['table' => $table, 'config' => $config];
    }
}

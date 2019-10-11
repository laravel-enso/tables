<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Config;

trait Action
{
    use ProvidesRequest;

    public function __invoke(Request $request)
    {
        $request = new $this->request($request);
        $table = App::make($this->tableClass, ['request' => $request]);
        $template = (new TemplateLoader($table))->handle();
        $config = new Config($request, $template);

        App::make($this->actionClass, [
            'table' => $table,
            'request' => $request,
        ])->handle();
    }
}

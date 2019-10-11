<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Config;
use LaravelEnso\Tables\app\Services\TemplateLoader;
use LaravelEnso\Tables\app\Services\Excel as Service;

trait Excel
{
    use ProvidesRequest;

    public function __invoke(Request $request)
    {
        $user = $request->user();
        $request = $this->request($request);
        $table = App::make($this->tableClass, ['request' => $request]);
        $template = (new TemplateLoader($table))->handle();
        $config = new Config($request, $template);

        (new Service(
            $user, $config, $this->tableClass
        ))->handle();
    }
}

<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Facades\App;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Builders\Export;

class Fetcher
{
    private $request;
    private $builder;
    private $data;
    private $page = 0;

    public function __construct(string $class, array $request)
    {
        $request = new Request($request, true);

        $this->builder = (new Export(
            App::make($class, ['request' => $request]),
            $request
        ))->fetcher();

        $this->request = new Obj($request);
    }

    public function name()
    {
        $this->request->get('name');
    }

    public function data()
    {
        return $this->data;
    }

    public function chunkSize()
    {
        return $this->data->count();
    }

    public function next()
    {
        $this->data = $this->builder->fetch($this->page++);
    }

    public function valid()
    {
        return $this->data->isNotEmpty();
    }

    public function request()
    {
        return $this->request;
    }
}

<?php

namespace LaravelEnso\Tables\app\Services;

use LaravelEnso\Helpers\app\Classes\Obj;

abstract class Action
{
    private $fetcher;
    private $request;

    public function __construct(string $class, array $request)
    {
        $this->fetcher = $this->fetcher = new Fetcher($class, $request);
        $this->request = new Obj($request);
    }

    abstract public function process(array $row);

    public function handle()
    {
        $this->fetcher->next();

        while ($this->fetcher->valid()) {
            $this->fetcher->data()->each(function ($row) {
                $this->process($row);
            });

            $this->fetcher->next();
        }

        return $this;
    }

    public function request()
    {
        return $this->request;
    }
}

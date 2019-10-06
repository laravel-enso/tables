<?php

namespace LaravelEnso\Tables\app\Services;

use BadMethodCallException;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\Tables\app\Services\Template\Builder;
use LaravelEnso\Tables\app\Services\Template\Validator;

class Template
{
    private const ProxiedMethods = ['get', 'has'];

    private $template;
    private $table;
    private $meta;
    private $ready;

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->meta = new Obj();
        $this->ready = false;
    }

    public function handle()
    {
        $this->build();

        return [
            'template' => $this->template,
            'meta' => $this->meta,
            'apiVersion' => config('enso.tables.apiVersion'),
        ];
    }

    public function build()
    {
        if (! $this->ready) {
            $this->template = $this->parse($this->table->templatePath());
            (new Builder($this->template, $this->meta))->handle();

            $this->ready = true;
        }
    }

    public function load($cache)
    {
        [
            'meta' => $this->meta,
            'template' => $this->template
        ] = $cache;

        return $this;
    }

    public function __call($method, $args)
    {
        if (collect(self::ProxiedMethods)->contains($method)) {
            return $this->template->{$method}(...$args);
        }

        throw new BadMethodCallException('Method '.static::class.'::'.$method.'() not found');
    }

    private function parse($filename)
    {
        $template = new Obj(
            (new JsonParser($filename))->array()
        );

        if ($this->needsValidation()) {
            (new Validator($template))->run();
        }

        return $template;
    }

    private function needsValidation()
    {
        return ! app()->environment('production')
            || config('enso.tables.validations') === 'always';
    }
}

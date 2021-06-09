<?php

namespace LaravelEnso\Tables\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\JsonReader;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Template\Builder;
use LaravelEnso\Tables\Services\Template\Validator;
use ReflectionClass;

class Template
{
    private Builder $builder;
    private Obj $template;
    private Obj $meta;

    public function __construct(private Table $table)
    {
        $this->meta = new Obj();
    }

    public function __call($method, $args)
    {
        return $this->template->{$method}(...$args);
    }

    public function load(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;

        return $this;
    }

    public function buildCacheable()
    {
        $this->template = $this->template();

        $this->builder()->handleCacheable();

        return $this;
    }

    public function buildNonCacheable()
    {
        $this->builder()->handleNonCacheable();

        return $this;
    }

    public function toArray()
    {
        return [
            'template' => $this->template,
            'meta' => $this->meta,
            'apiVersion' => Config::get('enso.tables.apiVersion'),
        ];
    }

    public function columns()
    {
        return $this->template->get('columns');
    }

    public function buttons(): Collection
    {
        return $this->template->get('buttons');
    }

    public function meta(): Obj
    {
        return $this->meta;
    }

    public function column(string $index)
    {
        return $this->columns()[$index];
    }

    private function builder()
    {
        return $this->builder
            ??= new Builder($this->template, $this->meta);
    }

    private function template()
    {
        $template = $this->readJson($this->table->templatePath());
        $model = $this->table->query()->getModel();

        if (! $template->has('model')) {
            $this->setModel($template, $model);
        }

        $this->setTable($template, $model);

        return $template;
    }

    private function setModel(Obj $template, Model $model)
    {
        $model = (new ReflectionClass($model))->getShortName();

        $template->set('model', Str::camel($model));
    }

    private function setTable(Obj $template, Model $model): self
    {
        $template->set('table', $model->getTable());

        return $this;
    }

    private function readJson($path)
    {
        $template = new Obj(
            (new JsonReader($path))->array()
        );

        if ($this->needsValidation()) {
            (new Validator($template, $this->table))->run();
        }

        return $template;
    }

    private function needsValidation()
    {
        $validations = Config::get('enso.tables.validations');

        return in_array($validations, [App::environment(), 'always']);
    }
}

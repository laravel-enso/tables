<?php

namespace LaravelEnso\Tables\app\Services\Data;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template;

class Config
{
    private const TemplateProxy = [
        'appends', 'comparisonOperator', 'countCache', 'flatten', 'fullInfoRecordLimit', 'name',
    ];

    private const RequestMeta = [
        'search', 'visible', 'searchMode', 'start', 'length', 'translate', 'forceInfo', 'sort',
    ];

    private const RequestColumnMeta = ['visible', 'sort', 'hidden'];

    private $request;
    private $template;
    private $columns;
    private $meta;

    public function __construct(Request $request, Template $template)
    {
        $this->request = $request;
        $this->template = $template;

        $this->setMeta()
            ->setColumns();
    }

    public function meta()
    {
        return $this->meta;
    }

    public function columns()
    {
        return $this->columns;
    }

    public function filters()
    {
        return $this->request->filters();
    }

    public function intervals()
    {
        return $this->request->intervals();
    }

    public function params()
    {
        return $this->request->params();
    }

    public function template()
    {
        return $this->template;
    }

    public function request()
    {
        return $this->request;
    }

    public function __call($method, $args)
    {
        if (isset($args[0]) && collect(self::TemplateProxy)->contains($args[0])) {
            return $this->template->{$method}(...$args);
        }

        return $this->request->{$method}(...$args);
    }

    private function setMeta()
    {
        $this->meta = $this->template->meta()
            ->forget(static::RequestMeta)
            ->merge(
                $this->request->meta()
                    ->intersectByKeys(collect(static::RequestMeta)->flip())
            );

        return $this;
    }

    private function setColumns()
    {
        $this->columns = $this->request->columns()
            ->map(function ($column, $index) {
                return $this->mergeColumnMeta($this->template->column($index), $column);
            });

        Computors::columns($this);
    }

    private function mergeColumnMeta(Obj $templateColumn, Obj $requestColumn)
    {
        $meta = $templateColumn->get('meta', collect())
            ->forget(static::RequestColumnMeta)
            ->merge($this->requestColumnMeta($requestColumn));

        return $templateColumn->set('meta', $meta);
    }

    private function requestColumnMeta(Obj $requestColumn)
    {
        return $requestColumn->get('meta', collect())
            ->intersectByKeys(collect(static::RequestColumnMeta)->flip());
    }
}

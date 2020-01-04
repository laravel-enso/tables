<?php

namespace LaravelEnso\Tables\App\Services\Data;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Services\Template;

class Config
{
    private const TemplateProxy = [
        'appends', 'comparisonOperator', 'countCache', 'flatten', 'fullInfoRecordLimit', 'name',
    ];

    private const RequestMeta = [
        'search', 'visible', 'searchMode', 'start', 'length', 'translate', 'forceInfo', 'sort',
    ];

    private const RequestColumnMeta = ['visible', 'sort', 'hidden'];

    private Request $request;
    private Template $template;
    private Obj $columns;
    private Obj $meta;

    public function __construct(Request $request, Template $template)
    {
        $this->request = $request;
        $this->template = $template;

        $this->setMeta()
            ->setColumns();
    }

    public function __call($method, $args)
    {
        if (isset($args[0]) && (new Collection(self::TemplateProxy))->contains($args[0])) {
            return $this->template->{$method}(...$args);
        }

        return $this->request->{$method}(...$args);
    }

    public function meta(): Obj
    {
        return $this->meta;
    }

    public function columns(): Obj
    {
        return $this->columns;
    }

    public function filters(): Obj
    {
        return $this->request->filters();
    }

    public function intervals(): Obj
    {
        return $this->request->intervals();
    }

    public function params(): Obj
    {
        return $this->request->params();
    }

    public function template(): Template
    {
        return $this->template;
    }

    public function request(): Request
    {
        return $this->request;
    }

    private function setMeta(): self
    {
        $requestMeta = new Collection(static::RequestMeta);

        $this->meta = $this->template->meta()
            ->forget(static::RequestMeta)
            ->merge($this->request->meta()->intersectByKeys($requestMeta->flip()));

        return $this;
    }

    private function setColumns(): void
    {
        $this->columns = $this->request->columns()
            ->map(fn ($column, $index) => $this->mergeColumnMeta(
                $this->template->column($index), $column
            ));

        Computors::columns($this);
    }

    private function mergeColumnMeta(Obj $templateColumn, Obj $requestColumn): Obj
    {
        $meta = $templateColumn->get('meta', new Collection())
            ->forget(static::RequestColumnMeta)
            ->merge($this->requestColumnMeta($requestColumn));

        return $templateColumn->set('meta', $meta);
    }

    private function requestColumnMeta(Obj $requestColumn): Collection
    {
        return $requestColumn->get('meta', new Collection())
            ->intersectByKeys((new Collection(static::RequestColumnMeta))->flip());
    }
}

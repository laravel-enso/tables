<?php

namespace LaravelEnso\Tables\Services\Data;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config as ConfigFacade;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Template;

class Config
{
    private const TemplateProxy = [
        'appends', 'comparisonOperator', 'countCache', 'flatten', 'fullInfoRecordLimit',
        'name', 'strip', 'table',
    ];

    private const RequestMeta = [
        'search', 'visible', 'searchMode', 'start', 'length', 'translate', 'forceInfo', 'sort',
    ];

    private const RequestColumnMeta = ['visible', 'sort', 'hidden'];

    private const RemoveDefaultColumnMeta = ['sort'];

    private Obj $columns;
    private Obj $meta;

    public function __construct(
        private Request $request,
        private Template $template
    ) {
        $this->setMeta()
            ->setColumns();
    }

    public function __call($method, $args)
    {
        if (isset($args[0]) && Collection::wrap(self::TemplateProxy)->contains($args[0])) {
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

    public function searches(): Obj
    {
        return $this->request->searches();
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

    public function isEnso(): bool
    {
        return ! empty(ConfigFacade::get('enso.config'));
    }

    public function name(): string
    {
        $name = Str::of($this->get('name'))->snake();

        return preg_replace('/[^A-Za-z0-9_.-]/', '_', $name);
    }

    public function label(): string
    {
        return Str::of($this->name())->replace('_', ' ')->ucfirst();
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
        $this->removeDefaults();

        $this->columns = $this->template->columns()
            ->map(fn ($column) => $this->mergeColumnMeta($column));

        ArrayComputors::columns($this);
    }

    private function mergeColumnMeta(Obj $templateColumn): Obj
    {
        $requestColumn = $this->request->column($templateColumn->get('name'));

        if ($requestColumn) {
            $meta = $templateColumn->get('meta', new Collection())
                ->forget(static::RequestColumnMeta)
                ->merge($this->requestColumnMeta($requestColumn));

            $templateColumn->set('meta', $meta);
        }

        return $templateColumn;
    }

    private function requestColumnMeta(Obj $requestColumn): Collection
    {
        return $requestColumn->get('meta', new Collection())
            ->intersectByKeys(Collection::wrap(static::RequestColumnMeta)->flip());
    }

    private function removeDefaults(): void
    {
        $this->template->columns()
            ->map(fn ($column) => tap($column, fn ($column) => $column->get('meta')
                ->forget(static::RemoveDefaultColumnMeta)));
    }
}

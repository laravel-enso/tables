<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\VueDatatable\app\Classes\Table\Builder;

abstract class Table
{
    protected $request;
    protected $templatePath;

    public function __construct(array $request = [])
    {
        $this->request = new Obj($request);
    }

    abstract public function query();

    public function request()
    {
        return $this->request;
    }

    /**
     * init VueDatatable.
     *
     * @return array
     */
    public function init()
    {
        return ['template' => $this->getTemplate($this->templatePath())];
    }

    public function data()
    {
        return $this->builder()
            ->data();
    }

    public function excel()
    {
        return $this->builder()
            ->excel();
    }

    public function fetcher(int $chunk)
    {
        return $this->builder()
            ->fetcher($chunk);
    }

    public function templatePath()
    {
        return $this->templatePath;
    }

    private function builder()
    {
        // Call query before builder
        // Sometimes we need declare template path in current request
        $query = $this->query();

        return new Builder($this->request, $query, $this->getTemplate($this->templatePath()));
    }

    /**
     * Get template object.
     *
     * @param string $path - path to template.json
     *
     * @return \stdClass
     */
    protected function getTemplate($path)
    {
        return (new Template($path))->get();
    }
}

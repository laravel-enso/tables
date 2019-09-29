<?php

namespace LaravelEnso\Tables\app\Services;

use Str;
use Cache;
use Illuminate\Cache\TaggableStore;
use LaravelEnso\Tables\app\Contracts\Table;

class TemplateCache
{
    private $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function get()
    {
        return $this->load()
            ?: $this->store((new Template($this->table))->get());
    }

    public function store($template)
    {
        if (config('enso.tables.template_cache')
            && $template['template']->get('template_cache')) {
            $this->cache()->put($this->cacheKey(), $template);
        }

        return $template;
    }

    public function load()
    {
        if (! config('enso.tables.template_cache')) {
            return;
        }

        return $this->cache()->get($this->cacheKey());
    }

    private function cacheKey(): string
    {
        return config('enso.tables.cache_prefix')
            .':'.Str::slug($this->table->templatePath());
    }

    private function cache()
    {
        return Cache::getStore() instanceof TaggableStore
            ? Cache::tags(config('enso.tables.cache_tags'))
            : Cache::store(Cache::getDefaultDriver());
    }
}

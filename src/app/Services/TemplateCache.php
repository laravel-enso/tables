<?php

namespace LaravelEnso\Tables\app\Services;

use Cache;
use Illuminate\Cache\TaggableStore;

class TemplateCache
{
    private $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    public function get()
    {
        return $this->load()
            ?: $this->store($this->template->get());
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
            .':'.$this->template->filename();
    }

    private function cache()
    {
        return Cache::getStore() instanceof TaggableStore
            ? Cache::tags(config('enso.tables.cache_tags'))
            : Cache::store(Cache::getDefaultDriver());
    }
}

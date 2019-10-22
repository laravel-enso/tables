<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Str;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Contracts\DynamicTemplate;

class TemplateLoader
{
    private $table;
    private $template;
    private $cache;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function handle()
    {
        $this->load();

        return $this->template;
    }

    private function load()
    {
        $this->template = $this->fromCache() ?? $this->new();

        if ($this->shouldCache()) {
            $this->cache()->put($this->cacheKey(), $this->template->toArray());
        }

        $this->template->buildNonCacheable();
    }

    private function fromCache()
    {
        if (! $this->cache()->has($this->cacheKey())) {
            return null;
        }

        $this->cache = $this->cache()->get($this->cacheKey());

        return (new Template($this->table))
            ->load($this->cache['template'], $this->cache['meta']);
    }

    private function new()
    {
        return (new Template($this->table))->buildCacheable();
    }

    private function shouldCache()
    {
        if (isset($this->cache)) {
            return false;
        }

        $type = $this->template->get(
            'templateCache', config('enso.tables.cache.template')
        );

        switch ($type) {
            case 'never':
                return false;
            case 'always':
                return true;
            default:
                return app()->environment($type);
        }
    }

    private function cacheKey(): string
    {
        $prefix = $this->table instanceof DynamicTemplate
            ? $this->table->cachePrefix().'-'
            : null;

        return config('enso.tables.cache.prefix')
            .':'.$prefix
            .Str::slug(str_replace(
                ['/', '.'], [' ', ' '], $this->table->templatePath()
            ));
    }

    private function cache()
    {
        return Cache::getStore() instanceof TaggableStore
            ? Cache::tags(config('enso.tables.cache.tag'))
            : Cache::store();
    }
}

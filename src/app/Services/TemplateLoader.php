<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Str;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\app\Contracts\Table;

class TemplateLoader
{
    private $table;
    private $template;

    public static function load(Table $table)
    {
        return (new self($table))->template();
    }

    private function __construct(Table $table)
    {
        $this->table = $table;
        $this->template = new Template($table);
        $this->loadCache();
    }

    private function template()
    {
        return $this->template;
    }

    private function loadCache()
    {
        if ($cache = $this->cache()->get($this->cacheKey())) {
            return $this->template->load($cache);
        }

        $this->template->build();

        if ($this->shouldCache()) {
            $this->cache()->put($this->cacheKey(), $this->template->handle());
        }
    }

    private function shouldCache()
    {
        $type = $this->template->get('templateCache',
            config('enso.tables.cache.template'));

        switch ($type) {
            case 'never':
                return false;
            case 'always':
                return true;
            default :
                return app()->environment($type);
        }
    }

    private function cacheKey(): string
    {
        return config('enso.tables.cache.prefix')
            .':'.Str::slug(str_replace(
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

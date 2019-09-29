<?php

namespace Services\Template\Builders;

use Cache;
use Config;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\TemplateCache;

class TemplateCacheTest extends TestCase
{
    private $templateCache;

    protected function setUp() :void
    {
        parent::setUp();

        $this->templateCache = (new TemplateCache(new TemplateDummy('test')));

        Config::set('enso.tables.cache_prefix', 'prefix');
        Config::set('enso.tables.cache_tags', 'tag');
        Config::set('enso.tables.template_cache', true);
    }

    /** @test */
    public function can_get_template()
    {
        $this->assertArrayHasKey('test_key', $this->templateCache->get());
    }

    /** @test */
    public function can_store_template()
    {
        $this->templateCache->get();

        $this->assertArrayHasKey('test_key', Cache::tags(['tag'])->get('prefix:test'));
    }

    /** @test */
    public function cannot_store_template_with_disabled_cache()
    {
        Config::set('enso.tables.template_cache', false);

        $this->templateCache->get();

        $this->assertNull(Cache::tags(['tag'])->get('prefix:test'));
    }

    /** @test */
    public function cannot_store_template_with_template_disabled_cache()
    {
        TemplateDummy::$cache = false;

        $this->templateCache->get();

        $this->assertNull(Cache::tags(['tag'])->get('prefix:test'));
    }
}

class TemplateDummy extends Template
{
    public static $cache = true;

    public function __construct(string $filename)
    {
        self::$cache = true;
    }

    public function get()
    {
        return [
            'template' => new Obj(['template_cache' => self::$cache]),
            'test_key' => true
        ];
    }

    public function filename()
    {
        return 'test';
    }
}

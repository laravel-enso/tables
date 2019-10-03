<?php

namespace Services\Template\Builders;

use Str;
use Cache;
use Route;
use Config;
use Tests\TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\TemplateCache;

class TemplateCacheTest extends TestCase
{
    private $templateCache;

    private $table;

    protected function setUp() :void
    {
        parent::setUp();

        Route::any('route')->name('test_tables.tableData');
        Route::getRoutes()->refreshNameLookups();

        $this->table = new TableDummy();
        $this->templateCache = (new TemplateCache($this->table));

        Config::set('enso.tables.cache.prefix', 'prefix');
        Config::set('enso.tables.cache.tag', 'tag');
        Config::set('enso.tables.cache.template', true);
    }

    /** @test */
    public function can_get_template()
    {
        $this->assertTemplate($this->templateCache->get());
    }

    /** @test */
    public function can_store_template()
    {
        $this->templateCache->get();

        $this->assertTemplate(Cache::tags(['tag'])->get($this->cacheKey()));
    }

    /** @test */
    public function cannot_store_template_with_disabled_cache()
    {
        Config::set('enso.tables.cache.template', false);

        $this->templateCache->get();

        $this->assertNull(Cache::tags(['tag'])->get($this->cacheKey()));
    }

    /** @test */
    public function cannot_store_template_with_template_disabled_cache()
    {
        TableDummy::$cache = false;

        $this->templateCache->get();

        $this->assertNull(Cache::tags(['tag'])->get($this->cacheKey()));
    }

    private function assertTemplate($result)
    {
        $this->assertEquals('test_name',
            $result['template']->get('columns')->first()->get('name'));
    }

    private function cacheKey(): string
    {
        return 'prefix:'.Str::slug($this->table->templatePath());
    }
}

class TableDummy implements Table
{
    public static $cache = true;

    public function __construct()
    {
        self::$cache = true;
    }

    public function query(): Builder
    {
        return App::make(Builder::class);
    }

    public function templatePath(): string
    {
        if (self::$cache) {
            return __DIR__.'/stubs/template.json';
        }
        return __DIR__.'/stubs/template_without_cache.json';
    }
}

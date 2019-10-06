<?php

namespace Services\Template\Builders;

use File;
use Cache;
use Route;
use Config;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\TemplateLoader;

class TemplateLoaderTest extends TestCase
{
    private $table;

    protected function setUp() :void
    {
        parent::setUp();

        Route::any('route')->name('test_tables.tableData');
        Route::getRoutes()->refreshNameLookups();

        $this->table = new TableDummy();

        Config::set('enso.tables.cache.prefix', 'prefix');
        Config::set('enso.tables.cache.tag', 'tag');
        Config::set('enso.tables.cache.template', 'never');
    }

    /** @test */
    public function can_get_template()
    {
        $this->assertTemplate(TemplateLoader::load($this->table)->handle());
    }

    /** @test */
    public function can_cache_template()
    {
        TableDummy::cache('always');

        TemplateLoader::load($this->table)->handle();

        $this->assertTemplate(Cache::tags(['tag'])->get($this->cacheKey()));
    }

    /** @test */
    public function cannot_cache_template_with_never_cache_config()
    {
        Config::set('enso.tables.cache.template', 'never');
        TableDummy::cache(null);

        TemplateLoader::load($this->table)->handle();

        $this->assertNull(Cache::tags(['tag'])->get($this->cacheKey()));
    }

    /** @test */
    public function cannot_cache_template_with_never_template_cache()
    {
        Config::set('enso.tables.cache.template', 'always');
        TableDummy::cache('never');

        TemplateLoader::load($this->table)->handle();

        $this->assertNull(Cache::tags(['tag'])->get($this->cacheKey()));
    }
    /** @test */
    public function can_cache_with_environment()
    {
        Config::set('enso.tables.cache.template', app()->environment());
        TableDummy::cache(null);

        TemplateLoader::load($this->table)->handle();

        $this->assertTemplate(Cache::tags(['tag'])->get($this->cacheKey()));
    }

    private function assertTemplate($result)
    {
        $this->assertEquals('test_name',
            $result['template']->get('columns')->first()->get('name'));
    }

    private function cacheKey(): string
    {
        return config('enso.tables.cache.prefix')
        .':'.Str::slug(str_replace(
            ['/', '.'], [' ', ' '], $this->table->templatePath()
        ));
    }
}

class TableDummy implements Table
{
    private static $path = __DIR__.'/stubs/template.json';

    public function __construct()
    {
        self::cache('never');
    }

    public function query(): Builder
    {
        return App::make(Builder::class);
    }

    public static function cache($type)
    {
        $template = collect(json_decode(File::get(self::$path), true));
        $template->forget('templateCache');

        if ($type !== null) {
            $template['templateCache'] = $type;
        }

        File::put(self::$path, $template->toJson(JSON_PRETTY_PRINT));
    }

    public function templatePath(): string
    {
        return self::$path;
    }
}

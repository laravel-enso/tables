<?php

namespace LaravelEnso\Tables\Tests\units\Traits;

use Faker\Factory;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\Tables\Traits\TableCache;
use Tests\TestCase;

class TableCacheTest extends TestCase
{
    private $testModel;
    private $faker;
    private string $key;

    protected function setUp() :void
    {
        parent::setUp();

        Config::set('enso.tables.cache.prefix', 'prefix');

        $this->key = 'prefix:test_models';
        $this->faker = Factory::create();

        $this->createTestModelTable();

        $this->testModel = $this->createTestModel();

        $this->cache()->put($this->key, 1, now()->addHour());
    }

    /** @test */
    public function should_forgot_cache_when_model_is_deleted()
    {
        $this->testModel->delete();

        $this->assertFalse(Cache::has($this->key));
    }

    /** @test */
    public function should_forgot_cache_when_model_is_created()
    {
        $this->createTestModel();

        $this->assertFalse(Cache::has($this->key));
    }

    private function createTestModelTable()
    {
        Schema::create('test_models', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    private function createTestModel()
    {
        return TestModel::create([
            'name' => $this->faker->name,
        ]);
    }

    private function cache()
    {
        return Cache::getStore() instanceof TaggableStore
            ? Cache::tags($this->key)
            : Cache::store();
    }
}

class TestModel extends Model
{
    use TableCache;

    protected $fillable = ['name'];

    public function getTable()
    {
        return 'test_models';
    }
}

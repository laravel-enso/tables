<?php

namespace LaravelEnso\Tables\Tests\units\Traits;

use Cache;
use Config;
use Schema;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Tables\app\Traits\TableCache;

class TableCacheTest extends TestCase
{
    private $testModel;
    private $faker;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();
        Config::set('enso.tables.cache.prefix', 'prefix');

        $this->faker = Factory::create();

        $this->createTestModelTable();

        $this->testModel = $this->createTestModel();
    }

    /** @test */
    public function should_forgot_cache_when_model_is_deleted()
    {
        Cache::shouldReceive('forget')->with('prefix:test_models');

        $this->testModel->delete();
    }

    /** @test */
    public function should_forgot_cache_when_model_is_created()
    {
        Cache::shouldReceive('forget')->with('prefix:test_models');

        $this->createTestModel();
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



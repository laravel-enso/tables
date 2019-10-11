<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\Tests\units\Services\SetUp;
use LaravelEnso\Tables\Tests\units\Services\TestModel;
use LaravelEnso\Tables\app\Services\Table\Builders\Meta;

class MetaTest extends TestCase
{
    use SetUp;

    /** @test */
    public function can_get_data()
    {
        $response = $this->requestResponse();

        $this->assertEquals(TestModel::count(), $response->get('count'));
    }

    /** @test */
    public function cannot_get_data_cache_count()
    {
        $this->config->put('countCache', false);

        $this->requestResponse();

        $this->assertFalse(Cache::has('enso:tables:testModels'));
    }

    /** @test */
    public function can_get_data_with_cache_when_table_cache_trait_used()
    {
        Config::set('enso.tables.cache.count', true);

        $this->requestResponse();

        $this->assertEquals(1, Cache::get('enso:tables:test_models'));
    }

    /** @test */
    public function can_get_data_with_limit()
    {
        $this->config->meta()->put('length', 0);

        $response = $this->requestResponse();

        $this->assertEquals(null, $response->get('filtered'));
        $this->assertEquals(1, $response->get('count'));
    }

    /** @test */
    public function can_get_data_with_total()
    {
        $this->createTestModel();

        $this->config->columns()->push(new Obj([
            'name' => 'price',
            'data' => 'price',
            'meta' => ['total' => true],
        ]));

        $this->config->meta()->put('total', true);

        $response = $this->requestResponse();

        $this->assertEquals(
            TestModel::sum('price'),
            $response->get('total')->get('price')
        );
    }

    /** @test */
    public function can_use_full_info_record_limit()
    {
        $limit = 1;

        $this->createTestModel();

        $this->testModel->update(['name' => 'User']);

        $this->config->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['searchable' => true]
        ]));

        $this->config->set('comparisonOperator', 'LIKE');

        $this->config->meta()->set('search', $this->testModel->name)
            ->set('fullInfoRecordLimit', $limit)
            ->set('length', $limit)
            ->set('searchMode', 'full');

        $response = $this->requestResponse();

        $this->assertFalse($response->get('fullRecordInfo'));
        $this->assertEquals(2, $response->get('count'));
        $this->assertEquals(null, $response->get('filtered'));
    }

    private function requestResponse()
    {
        $builder = new Meta($this->table, $this->config);

        return new Obj($builder->toArray());
    }
}


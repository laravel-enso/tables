<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use Tests\TestCase;
use LaravelEnso\Tables\Tests\units\Services\SetUp;
use LaravelEnso\Tables\Tests\units\Services\TestModel;
use LaravelEnso\Tables\app\Services\Data\Filters\Search;

class SearchTest extends TestCase
{
    use SetUp;

    /** @test */
    public function can_get_data_without_condition()
    {
        $response = $this->requestResponse();

        $this->assertCount(TestModel::count(), $response);

        $this->assertTrue(
            $response->pluck('name')
                ->contains($this->testModel->name)
        );
    }

    /** @test */
    public function can_use_search()
    {
        $this->config->meta()->set('search', $this->testModel->name);

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );

        $this->config->meta()->set('search', $this->testModel->name.'-');

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    /** @test */
    public function can_use_starts_with_search()
    {
        $this->config->meta()->set(
            'search', 
            collect(explode(' ', $this->testModel->name))->first()
        )->set('searchMode', 'startsWith');

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );
    }

    /** @test */
    public function can_use_ends_with_search()
    {
        $this->config->meta()->set(
            'search', 
            collect(explode(' ', $this->testModel->name))->last()
        )->set('searchMode', 'endsWith');

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );
    }

    /** @test */
    public function can_use_multi_argument_search()
    {
        $this->config->columns()->push([
            'data' => 'color',
            'name' => 'color',
            'meta' => ['searchable' => true]
        ]);

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );
    }

    private function requestResponse()
    {
        $query = $this->table->query();

        (new Search($this->table, $this->config, $query))->handle();

        return $query->get();
    }
}

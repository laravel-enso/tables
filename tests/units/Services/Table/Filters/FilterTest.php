<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\Tests\units\Services\SetUp;
use LaravelEnso\Tables\app\Services\Table\Filters\Filter;

class FilterTest extends TestCase
{
    use SetUp;

    /** @test */
    public function can_use_filters()
    {
        $filters = new Obj(['name' => $this->testModel->name]);

        $this->config->filters()->set('test_models', $filters);

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );

        $filters->set('name', $this->testModel->name.'-');

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    private function requestResponse()
    {
        $query = $this->table->query();

        (new Filter($this->table, $this->config, $query))->handle();

        return $query->get();
    }
}

<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\FilterAggregator;
use LaravelEnso\Tables\Services\Data\Request;
use LaravelEnso\Tables\Services\Data\Filters\Filter;
use LaravelEnso\Tables\Services\Template;
use LaravelEnso\Tables\Tests\units\Services\SetUp;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FilterTest extends TestCase
{
    use SetUp;

    #[Test]
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

    #[Test]
    public function can_use_json_encoded_filters()
    {
        $aggregator = new FilterAggregator([], json_encode([
            'test_models' => ['name' => $this->testModel->name],
        ]), [], []);

        $request = new Request([], [
            'length' => 10,
            'search' => '',
            'searchMode' => 'full',
        ], $aggregator());

        $config = new Config($request, (new Template($this->table))
            ->buildCacheable()->buildNonCacheable());
        $query = $this->table->query();

        (new Filter($this->table, $config, $query))->handle();

        $this->assertCount(1, $query->get());
    }

    #[Test]
    public function cannot_use_invalid_filters()
    {
        $filters = new Obj(['name' => null]);

        $this->config->filters()->set('test_models', $filters);

        $this->assertFalse($this->filter()->applies());
    }

    private function requestResponse()
    {
        $this->filter()->handle();

        return $this->query->get();
    }

    private function filter()
    {
        return new Filter($this->table, $this->config, $this->query);
    }
}

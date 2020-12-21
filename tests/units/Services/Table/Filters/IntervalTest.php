<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Data\Filters\Interval;
use LaravelEnso\Tables\Tests\units\Services\SetUp;
use Tests\TestCase;

class IntervalTest extends TestCase
{
    use SetUp;

    /** @test */
    public function can_use_interval()
    {
        $intervals = new Obj(['id' => [
            'min' => $this->testModel->id - 1,
            'max' => $this->testModel->id + 1,
        ]]);

        $this->config->intervals()->set('test_models', $intervals);

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $this->testModel->name,
            $response->first()->name
        );

        $intervals->get('id')
            ->set('min', $this->testModel->id - 2)
            ->set('max', $this->testModel->id - 1);

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    /** @test */
    public function can_use_date_interval()
    {
        $intervals = new Obj(['created_at' => [
            'min' => $this->testModel->created_at->subDays(360)->format('Y-m-d'),
            'max' => $this->testModel->created_at->addDays(1)->format('Y-m-d'),
        ]]);

        $this->config->intervals()->set('test_models', $intervals);

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $this->testModel->name,
            $response->first()->name
        );

        $intervals->get('created_at')
            ->set('min', $this->testModel->created_at->subDays(2)->format('Y-m-d'))
            ->set('max', $this->testModel->created_at->subDays(1)->format('Y-m-d'));

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    /** @test */
    public function can_use_half_interval()
    {
        $intervals = new Obj([
            'id' => [
                'min' => $this->testModel->id - 1,
                'max' => null,
            ],
        ]);

        $this->config->intervals()->set('test_models', $intervals);

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $this->testModel->name,
            $response->first()->name
        );
    }

    /** @test */
    public function cannot_use_invalid_intervals()
    {
        $intervals = new Obj([
            'id' => [
                'min' => null,
                'max' => null,
            ],
        ]);

        $this->config->intervals()->set('test_models', $intervals);

        $this->assertFalse($this->interval()->applies());
    }

    private function requestResponse()
    {
        $this->interval()->handle();

        return $this->query->get();
    }

    private function interval()
    {
        return new Interval($this->table, $this->config, $this->query);
    }
}

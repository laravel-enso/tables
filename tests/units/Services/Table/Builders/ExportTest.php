<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Illuminate\Support\Facades\App;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Data\Fetcher;
use LaravelEnso\Tables\Tests\units\Services\SetUp;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use SetUp;

    /** @test */
    public function can_get_export_data_with_translatable()
    {
        App::make('translator')->addJsonPath(__DIR__.'/lang');

        App::setLocale('lang');

        $this->testModel->update(['name' => 'should translate']);

        $this->config->meta()->set('translatable', true);

        $this->config->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['translatable' => true],
        ]));

        $response = $this->requestResponse();

        $this->assertEquals('translation', $response[0]['name']);
    }

    private function requestResponse()
    {
        $fetcher = new Fetcher($this->table, $this->config);

        $fetcher->next();

        return $fetcher->current();
    }
}

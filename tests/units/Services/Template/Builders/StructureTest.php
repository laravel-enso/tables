<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Builders;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Template\Builders\Structure;
use Route;
use Tests\TestCase;

class StructureTest extends TestCase
{
    private $meta;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        $this->createRoute();

        $this->template = new Obj([
            'routePrefix' => 'prefix',
            'dataRouteSuffix' => 'suffix',
            'model' => 'test',
        ]);

        $this->meta = new Obj([]);
    }

    /** @test */
    public function can_build_with_route()
    {
        $this->build();

        $this->assertEquals('/test', $this->template->get('readPath'));
    }

    /** @test */
    public function can_build_with_length_menu()
    {
        $options = [12, 24];

        $this->template->set('lengthMenu', $options);

        $this->build();

        $this->assertEquals($options[0], $this->meta->get('length'));
    }

    private function createRoute($name = 'prefix.suffix', $path = '/test'): \Illuminate\Routing\Route
    {
        $route = Route::any($path)->name($name);

        Route::getRoutes()->refreshNameLookups();

        return $route;
    }

    private function build(): void
    {
        (new Structure(
            $this->template,
            $this->meta
        ))->build();
    }
}

<?php

namespace Services\Template\Builders;

use Route;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template\Builders\Structure;

class StructureTest extends TestCase
{
    private $meta;

    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        $this->createRoute();

        $this->meta = new Obj([]);
    }


    /** @test */
    public function can_build_with_route()
    {
        $this->template = new Obj([
            'routePrefix' => 'prefix',
            'dataRouteSuffix' => 'suffix',
        ]);

        $this->build();

        $this->assertEquals("/test", $this->template->get("readPath"));
    }

    /** @test */
    public function can_build_with_length_menu()
    {
        $this->template = $this->basicTemplate([
            'lengthMenu'=>[
                24,12
            ]
        ]);

        $this->build();

        $this->assertEquals(24, $this->meta->get("length"));
    }

    /** @test */
    public function can_build_with_full_info_record_limit()
    {
        $this->template = $this->basicTemplate([
            'fullInfoRecordLimit'=>24
        ]);

        $this->build();

        $this->assertEquals(24, $this->meta->get("fullInfoRecordLimit"));

        $this->assertFalse($this->template->has("fullInfoRecordLimit"));
    }


    private function basicTemplate($template = [])
    {
        $baseTemplate = [
            'routePrefix' => 'prefix',
            'dataRouteSuffix' => 'suffix',
        ];
        return new Obj(array_merge($baseTemplate, $template));
    }

    private function createRoute($name = 'prefix.suffix', $path = '/test'): \Illuminate\Routing\Route
    {
        $r=  Route::any($path)->name($name);
        Route::getRoutes()->refreshNameLookups();
        return $r;
    }

    private function build(): void
    {
        $structure = new Structure(
            $this->template,
            $this->meta
        );

        $structure->build();
    }

}

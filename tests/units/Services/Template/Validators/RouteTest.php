<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\Route as Exception;
use LaravelEnso\Tables\app\Attributes\Structure as Attributes;
use LaravelEnso\Tables\app\Services\Template\Validators\Route;

class RouteTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->template = new Obj(collect(Attributes::Mandatory)->flip());
    }

    /** @test */
    public function cannot_validate_with_wrong_route()
    {
        $this->template->set('routePrefix', 'routePrefix');
        $this->template->set('dataRouteSuffix', 'dataRouteSuffix');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::notFound('routePrefix.dataRouteSuffix')->getMessage());

        $this->validate();
    }


    /** @test */
    public function can_validate()
    {
        \Route::any('route')->name('route.test');
        \Route::getRoutes()->refreshNameLookups();

        $this->template->set('routePrefix', 'route');
        $this->template->set('dataRouteSuffix', 'test');

        $this->validate();

        $this->assertTrue(true);

    }

    private function validate()
    {
        $this->validator = new Route($this->template);

        $this->validator->validate();
    }
}

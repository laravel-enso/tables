<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Structure as Attributes;
use LaravelEnso\Tables\Exceptions\Route as Exception;
use LaravelEnso\Tables\Services\Template\Validators\Route;
use Tests\TestCase;

class RouteTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj(Collection::wrap(Attributes::Mandatory)->flip());
    }

    /** @test */
    public function cannot_validate_with_invalid_route()
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

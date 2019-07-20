<?php

namespace Services\Template\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Attributes\Structure as Attributes;
use LaravelEnso\Tables\app\Services\Template\Validators\Route;

class RouteTest extends TestCase
{

    /** @test */
    public function cannot_validate_with_wrong_route()
    {
        $this->validate(
            [
                'routePrefix' => 'r',
                'dataRouteSuffix' => 'd'
            ],
            false
        );
    }


    /** @test */
    public function can_validate()
    {
        \Route::any('ROUTE_TEST')->name('route.test');
        \Route::getRoutes()->refreshNameLookups();

        $this->validate(
            $this->basicTemplate([
                'routePrefix' => 'route',
                'dataRouteSuffix' => 'test'
            ]),
            true
        );

    }

    private function basicTemplate($template = [])
    {
        $baseTemplate = array_flip(Attributes::Mandatory);
        return array_merge($baseTemplate, $template);
    }

    private function validate(array $template, bool $isValid)
    {
        $validator = new Route(
            new Obj($template)
        );

        if (!$isValid) {
            $this->expectException(TemplateException::class);
        } else {
            $this->assertTrue(true);
        }

        $validator->validate();
    }

}

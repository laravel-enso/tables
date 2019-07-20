<?php

namespace Services\Template\Validators;

use Route;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Attributes\Button as Attributes;
use LaravelEnso\Tables\app\Services\Template\Validators\Buttons;

class ButtonTest extends TestCase
{

    /** @test */
    public function cannot_validate_without_mandatory_attribute()
    {
        $this->validate(
            [
                'buttons' => [
                    []
                ],
            ],
            false
        );
    }


    /** @test */
    public function cannot_validate_with_wrong_attribute()
    {
        $this->validate(
            $this->basicTemplate([
                'wrong_attribute' => 'r',
            ]),
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_format()
    {
        $this->validate(
            $this->basicTemplate([
                [
                    'action' => true,
                ]
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                [
                    'action' => 'ajax',
                    'fullRoute' => '/'
                ]
            ]),
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_action()
    {
        $this->validate(
            $this->basicTemplate([
                [
                    'action' => 'WRONG_ACTION',
                    'fullRoute' => '/'
                ]
            ]),
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_route()
    {
        $this->validate(
            $this->basicTemplate([
                [
                    'action' => 'ajax',
                    'fullRoute' => '/',
                    'method' => 'post'
                ]
            ]),
            false
        );
    }


    /** @test */
    public function cannot_validate_with_wrong_method()
    {
        $this->validate(
            $this->basicTemplate([
                [
                    'action' => 'ajax',
                    'fullRoute' => $this->createRoute(),
                    'method' => 'WRONG_METHOD'
                ]
            ]),
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_button_type()
    {
        $this->validate(
            $this->basicTemplate([
                'UNKNOWN_TYPE',
            ]),
            false
        );

    }

    /** @test */
    public function can_validate()
    {
        $this->createRoute();

        $this->validate(
            $this->basicTemplate([
                [
                    'method' => 'GET',
                    'action' => 'ajax',
                    'fullRoute' => $this->createRoute()
                ],
            ]),
            true
        );
    }

    private function basicTemplate($buttons = [], $routePrefix = '')
    {
        if (!isset($buttons[0]))
            $buttons = [$buttons];

        $mandatoryAttributes = collect(Attributes::Mandatory)->flip()->map(function () {
            return new Obj([]);
        });

        $columns = collect($buttons)->map(function ($col) use ($mandatoryAttributes) {
            return new Obj($mandatoryAttributes->merge($col));
        });

        return [
            "buttons" => $columns,
            'routePrefix' => $routePrefix
        ];
    }

    private function validate(array $template, bool $isValid)
    {
        $validator = new Buttons(
            new Obj($template)
        );

        try {
            $validator->validate();

            if (!$isValid)
                $this->fail('should throw TemplateException');
        } catch (TemplateException $e) {
            if ($isValid)
                $this->fail('should not throw TemplateException' . PHP_EOL . $e);
        }

        $this->assertTrue(true);
    }

    private function createRoute()
    {
        Route::any('/test.button.create')->name('test.create');
        Route::getRoutes()->refreshNameLookups();

        return 'test.create';
    }

}

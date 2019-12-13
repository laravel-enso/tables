<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Route;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\Button as Exception;
use LaravelEnso\Tables\app\Attributes\Button as Attributes;
use LaravelEnso\Tables\app\Services\Template\Validators\Buttons;

class ButtonTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->template = new Obj(['buttons' => [$this->mockedButton()]]);
    }

    /** @test */
    public function cannot_validate_without_mandatory_attributes()
    {
        $this->template->get('buttons')->first()->forget('type');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::missingAttributes()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_attribute()
    {
        $this->template->get('buttons')->first()->set('wrong_attribute', 'wrong');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::unknownAttributes()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_action()
    {
        $this->template->get('buttons')->first()->set('action', true);

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::missingRoute()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_action_with_missing_method()
    {
        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', '/');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::missingMethod()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_route()
    {
        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', '/');
        $button->set('method', 'post');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::routeNotFound('/')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_method()
    {
        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', $this->createRoute());
        $button->set('method', 'WRONG_METHOD');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::invalidMethod('WRONG_METHOD')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_button_type()
    {
        $this->template->set('buttons', new Obj(['UNKNOWN_TYPE']));

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::undefined('UNKNOWN_TYPE')->getMessage());

        $this->validate();
    }

    /** @test */
    public function can_validate()
    {
        $this->createRoute();

        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', $this->createRoute());
        $button->set('method', 'GET');

        $this->validate();

        $this->assertTrue(true);
    }

    private function mockedButton()
    {
        return collect(Attributes::Mandatory)->reduce(function ($button, $attribute) {
            $button->set($attribute, new Obj());

            return $button;
        }, new Obj());
    }

    private function validate()
    {
        $this->validator = new Buttons($this->template);

        $this->validator->validate();
    }

    private function createRoute()
    {
        Route::any('/test.button.create')->name('test.create');
        Route::getRoutes()->refreshNameLookups();

        return 'test.create';
    }
}

<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Route;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\ButtonException;
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

        $this->expectException(ButtonException::class);

        $this->expectExceptionMessage(ButtonException::missingAttributes());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_attribute()
    {
        $this->template->get('buttons')->first()->set('wrong_attribute', 'wrong');

        $this->expectException(ButtonException::class);

        $this->expectExceptionMessage(ButtonException::unknownAttributes()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_action()
    {
        $this->template->get('buttons')->first()->set('action', true);

        $this->expectException(ButtonException::class);

        $this->expectExceptionMessage('Whenever you set an action for a button you need to provide the fullRoute or routeSuffix');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_action_with_missing_method()
    {
        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', '/');

        $this->expectException(ButtonException::class);

        $this->expectExceptionMessage('Whenever you set an ajax action for a button you need to provide the method aswell');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_route()
    {
        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', '/');
        $button->set('method', 'post');

        $this->expectException(ButtonException::class);

        $this->expectExceptionMessage('Button route does not exist: "/"');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_method()
    {
        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', $this->createRoute());
        $button->set('method', 'WRONG_METHOD');

        $this->expectException(ButtonException::class);

        $this->expectExceptionMessage('Method is incorrect: "WRONG_METHOD"');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_button_type()
    {
        $this->template->set('buttons', new Obj(['UNKNOWN_TYPE']));

        $this->expectException(ButtonException::class);

        $this->expectExceptionMessage('Unknown Button(s) Found: "UNKNOWN_TYPE"');

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

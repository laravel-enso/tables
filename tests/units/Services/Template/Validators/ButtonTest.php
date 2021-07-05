<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Button as Attributes;
use LaravelEnso\Tables\Contracts\ConditionalActions;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Exceptions\Button as Exception;
use LaravelEnso\Tables\Services\Template\Validators\Buttons\Buttons;
use LaravelEnso\Tables\Tests\units\Services\TestTable;
use Route;
use Tests\TestCase;

class ButtonTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj([
            'routePrefix' => 'mockedPrefix',
            'buttons' => [$this->mockedButton()],
        ]);
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
    public function cannot_validate_with_invalid_attribute()
    {
        $this->template->get('buttons')->first()->set('invalid_attribute', 'invalid');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::unknownAttributes()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_action()
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
    public function cannot_validate_with_invalid_route()
    {
        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', '/');
        $button->set('method', 'GET');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::routeNotFound('/')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_method()
    {
        $button = $this->template->get('buttons')->first();

        $button->set('action', 'ajax');
        $button->set('fullRoute', $this->createRoute());
        $button->set('method', 'invalid_method');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::invalidMethod('invalid_method')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_when_name_missing_for_conditional_actions()
    {
        $this->template->get('buttons')[0]->set('type', 'row');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::missingName()->getMessage());

        (new Buttons($this->template, $this->conditionalActionTable()))
            ->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_button_type()
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
        return Collection::wrap(Attributes::Mandatory)
            ->mapWithKeys(fn ($attribute) => [$attribute => $attribute]);
    }

    private function validate()
    {
        $this->validator = new Buttons($this->template, new TestTable());

        $this->validator->validate();
    }

    private function createRoute()
    {
        Route::any('/test.button.create')->name('test.create');
        Route::getRoutes()->refreshNameLookups();

        return 'test.create';
    }

    private function conditionalActionTable(): Table
    {
        return new class extends TestTable implements ConditionalActions
        {
            public function render(array $row, string $action): bool
            {
                return false;
            }
        };
    }
}

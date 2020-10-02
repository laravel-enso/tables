<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Button as Attributes;
use LaravelEnso\Tables\Contracts\ConditionalActions;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Exceptions\Button as Exception;
use LaravelEnso\Tables\Services\Template\Validators\Buttons\Buttons;
use Route;
use Tests\TestCase;

class ButtonTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->template = new Obj([
            'routePrefix' => 'mockedPrefix',
            'buttons' => [$this->mockedButton()]
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
    public function cannot_validate_when_name_not_applied_for_conditional_actions()
    {
        $button = $this->template->get('buttons')->first();

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::missingName()->getMessage());

        (new Buttons($this->template, $this->conditionalActionTable()))
            ->validate();
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
        return (new Collection(Attributes::Mandatory))
            ->mapWithKeys(fn ($attribute) => [$attribute => new Obj()]);
    }

    private function validate()
    {
        $this->validator = new Buttons($this->template, $this->dummyTable());

        $this->validator->validate();
    }

    private function createRoute()
    {
        Route::any('/test.button.create')->name('test.create');
        Route::getRoutes()->refreshNameLookups();

        return 'test.create';
    }

    private function dummyTable(): Table
    {
        return new class implements Table {
            public function query(): Builder
            {
                return Model::query();
            }

            public function templatePath(): string
            {
                return '';
            }
        };
    }

    private function conditionalActionTable(): Table
    {
        return new class implements Table, ConditionalActions {
            public function query(): Builder
            {
                return Model::query();
            }

            public function templatePath(): string
            {
                return '';
            }

            public function render(array $row, string $action): bool
            {
                return false;
            }
        };
    }
}

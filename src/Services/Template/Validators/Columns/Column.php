<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Columns;

use Illuminate\Support\Collection;
use LaravelEnso\Enums\Contracts\Select as Contract;
use LaravelEnso\Enums\Services\Enum;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Column as Attributes;
use LaravelEnso\Tables\Attributes\Number;
use LaravelEnso\Tables\Attributes\Style;
use LaravelEnso\Tables\Exceptions\Column as Exception;
use ReflectionClass;
use ReflectionEnum;

class Column
{
    private const Validations = [
        'mandatory', 'optional', 'align', 'class', 'enum',
        'meta', 'number', 'resource', 'tooltip',
    ];

    public function __construct(private Obj $column)
    {
    }

    public function validate(): void
    {
        Collection::wrap(self::Validations)
            ->each(fn ($validation) => $this->{$validation}());
    }

    private function mandatory(): void
    {
        $diff = Collection::wrap(Attributes::Mandatory)
            ->diff($this->column->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingAttributes($diff->implode('", "'));
        }
    }

    private function optional(): void
    {
        $attributes = Collection::wrap(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->column->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownAttributes($diff->implode('", "'));
        }
    }

    private function align(): void
    {
        if ($this->invalidAttribute('align', Style::Align)) {
            throw Exception::invalidAlign($this->column->get('name'));
        }
    }

    private function class(): void
    {
        if ($this->invalidString('class')) {
            throw Exception::invalidClass($this->column->get('name'));
        }
    }

    private function enum(): void
    {
        if ($this->column->has('enum')) {
            if ($this->enumNotFound()) {
                throw Exception::enumNotFound($this->column->get('enum'));
            } elseif ($this->invalidEnum()) {
                throw Exception::invalidEnum($this->column->get('enum'));
            }
        }
    }

    private function meta(): void
    {
        if ($this->column->has('meta')) {
            Meta::validate($this->column);
        }
    }

    private function number(): void
    {
        if ($this->invalidObject('number')) {
            throw Exception::invalidNumber($this->column->get('name'));
        }

        if ($this->invalidAttributes('number', Number::Optional)) {
            throw Exception::invalidNumberAttributes($this->column->get('name'));
        }
    }

    private function resource(): void
    {
        if ($this->missingClass('resource')) {
            throw Exception::resourceNotFound($this->column->get('resource'));
        }
    }

    private function tooltip(): void
    {
        if ($this->invalidString('tooltip')) {
            throw Exception::invalidTooltip($this->column->get('name'));
        }
    }

    private function missingClass(string $attribute): bool
    {
        return $this->column->has($attribute)
            && !class_exists($this->column->get($attribute));
    }

    public function enumNotFound(): bool
    {
        return !class_exists($this->column->get('enum'))
            && !enum_exists($this->column->get('enum'));
    }

    public function invalidEnum(): bool
    {
        return enum_exists($this->column->get('enum'))
            ? !(new ReflectionEnum($this->column->get('enum')))
                ->implementsInterface(Contract::class)
            : !(new ReflectionClass($this->column->get('enum')))
                ->isSubclassOf(Enum::class);
    }

    private function invalidString(string $attribute): bool
    {
        return $this->column->has($attribute)
            && !is_string($this->column->get($attribute));
    }

    private function invalidObject(string $attribute): bool //TODO can be aggregated with invalidAttributes
    {
        return $this->column->has($attribute)
            && !is_object($this->column->get($attribute));
    }

    private function invalidAttribute(string $attribute, array $allowed): bool
    {
        return $this->column->has($attribute)
            && !in_array($this->column->get($attribute), $allowed);
    }

    private function invalidAttributes(string $attribute, array $allowed): bool
    {
        return $this->column->has($attribute)
            && Collection::wrap($this->column->get($attribute))
            ->keys()->diff($allowed)->isNotEmpty();
    }
}

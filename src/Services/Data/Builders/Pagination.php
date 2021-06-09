<?php

namespace LaravelEnso\Tables\Services\Data\Builders;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\Helpers\Services\Obj;

class Pagination
{
    private const Computes = ['page', 'pages', 'atStart', 'atEnd', 'atMiddle', 'middlePages'];

    private ?int $pages;
    private bool $atStart;
    private bool $atEnd;
    private bool $atMiddle;
    private Collection $middlePages;
    private int $page;

    public function __construct(
        private Obj $meta,
        private int $filtered,
        private bool $fullInfo
    ) {
        $this->pages = null;
        $this->atStart = false;
        $this->atEnd = false;
        $this->atMiddle = false;
        $this->middlePages = new Collection();
    }

    public function toArray(): array
    {
        $this->handle();

        return [
            'page' => $this->page,
            'pages' => $this->pages,
            'atStart' => $this->atStart,
            'atEnd' => $this->atEnd,
            'atMiddle' => $this->atMiddle,
            'middlePages' => $this->middlePages,
        ];
    }

    private function handle(): void
    {
        Collection::wrap(self::Computes)
            ->each(fn ($method) => $this->{$method}());
    }

    private function page(): void
    {
        $this->page = $this->meta->get('start') / $this->meta->get('length') + 1;
    }

    private function pages(): void
    {
        if ($this->fullInfo) {
            $div = Decimals::div($this->filtered, $this->meta->get('length'));
            $this->pages = Decimals::ceil($div, 0);
        }
    }

    private function atStart(): void
    {
        if ($this->fullInfo) {
            $this->atStart = $this->page < 4;
        }
    }

    private function atEnd(): void
    {
        if ($this->fullInfo) {
            $this->atEnd = $this->pages - $this->page < 3;
        }
    }

    private function atMiddle(): void
    {
        if ($this->fullInfo) {
            $this->atMiddle = ! $this->atStart && ! $this->atEnd;
        }
    }

    private function middlePages(): void
    {
        if (! $this->fullInfo) {
            return;
        }

        if ($this->atStart) {
            $this->addStartPages();
        } elseif ($this->atEnd) {
            $this->addEndPages();
        } else {
            $this->addMiddlePages();
        }
    }

    private function addStartPages(): void
    {
        $max = min($this->pages - 1, 4);

        for ($i = 2; $i <= $max; $i++) {
            $this->middlePages->push($i);
        }
    }

    private function addEndPages(): void
    {
        if ($this->pages > 4) {
            $this->middlePages->push($this->pages - 3);
        }

        $this->middlePages->push($this->pages - 2, $this->pages - 1);
    }

    private function addMiddlePages(): void
    {
        $this->middlePages->push($this->page - 1, $this->page, $this->page + 1);
    }
}

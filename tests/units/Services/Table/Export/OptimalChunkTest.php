<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Export;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\Services\Data\Computors\OptimalChunk;
use Tests\TestCase;

class OptimalChunkTest extends TestCase
{
    /** @test */
    public function can_get_correct_chunk_within_threshold()
    {
        (new Collection(OptimalChunk::Thresholds))
            ->map(fn ($threshold, $index) => $this->map($threshold, $index))
            ->each(fn ($threshold) => $this->assertCorrectChunk($threshold));
    }

    /** @test */
    public function can_get_maximal_chunk_above_threshold()
    {
        $threshold = (new Collection(OptimalChunk::Thresholds))->pop();

        $this->assertEquals(OptimalChunk::get(++$threshold['limit']), OptimalChunk::MaxChunk);
    }

    private function map(array $threshold, int $index): array
    {
        $start = $index ? OptimalChunk::Thresholds[$index - 1]['limit'] : 0;

        return [
            'min' => $start,
            'max' => $threshold['limit'],
            'chunk' => $threshold['chunk'],
        ];
    }

    private function assertCorrectChunk(array $threshold)
    {
        $count = rand($threshold['min'], $threshold['max']);

        $this->assertEquals(OptimalChunk::get($count), $threshold['chunk']);
    }
}

<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Illuminate\Http\Resources\Json\JsonResource;
use LaravelEnso\Enums\App\Services\Enum;

class BuilderTestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'relation' => 'relation',
        ];
    }
}

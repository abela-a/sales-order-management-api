<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->collection,
            'links' => [
                'first' => $this->url(1),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $this->currentPage(),
                'from' => $this->firstItem(),
                'path' => $this->path(),
                'per_page' => $this->perPage(),
                'to' => $this->lastItem(),
            ],
        ];
    }
}

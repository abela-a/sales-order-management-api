<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderItemResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($item) {
            return [
                'quantity' => $item->quantity,

                $this->mergeWhen(
                    $item->relationLoaded('product'),
                    ['product' => $item->product],
                    [
                        'product_id' => $item->product_id,
                        'production_price' => $item->production_price,
                        'selling_price' => $item->selling_price,
                    ]
                ),
            ];
        })->values()->all();
    }
}

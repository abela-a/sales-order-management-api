<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $array = [
            'id' => $this->id,
            'reference_no' => $this->reference_no,

            $this->mergeWhen(
                $this->relationLoaded('customer'),
                ['customer' => new CustomerResource($this->customer)],
                ['customer_id' => $this->customer_id]
            ),
            $this->mergeWhen(
                $this->relationLoaded('sales'),
                ['sales' => new SalesResource($this->sales)],
                ['sales_id' => $this->sales_id]
            ),
            $this->mergeWhen(
                $this->relationLoaded('items'),
                ['items' => new OrderItemResource($this->items)],
            ),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        return array_filter($array, function ($value) {
            return $value !== null;
        });
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            $this->mergeWhen(
                $this->relationLoaded('user'),
                ['user' => new UserResource($this->user)],
                ['user_id' => $this->user_id]
            ),

            $this->mergeWhen(
                $this->relationLoaded('area'),
                ['area' => new SalesAreaResource($this->area)],
                ['area_id' => $this->area_id]
            ),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

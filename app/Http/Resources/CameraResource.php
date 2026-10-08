<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CameraResource extends JsonResource
{
    /**
     * Transformasi resource model Camera ke dalam array JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'brand'       => $this->brand,
            'daily_rate'  => (float) $this->daily_rate,
            'stock'       => (int) $this->stock,
            'image_path'  => $this->image,
            'image_url'   => $this->image ? asset('storage/' . $this->image) : null,
            'description' => $this->description,
            'status'      => $this->status,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
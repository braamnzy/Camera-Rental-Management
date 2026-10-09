<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PaymentResource;

class RentalResource extends JsonResource
{
    /**
     * Transformasi resource model Rental ke dalam array JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'camera_id'   => $this->camera_id,
            'start_date'  => is_string($this->start_date) ? $this->start_date : $this->start_date?->format('Y-m-d'),
            'end_date'    => is_string($this->end_date) ? $this->end_date : $this->end_date?->format('Y-m-d'),
            'total_days'  => (int) $this->total_days,
            'quantity'    => (int) ($this->quantity ?? 1),
            'total_price' => (float) $this->total_price,
            'status'      => $this->status,
            'pickup_date'   => is_string($this->pickup_date) ? $this->pickup_date : $this->pickup_date?->format('Y-m-d'),
            'pickup_time'   => $this->pickup_time,
            'pickup_method' => $this->pickup_method,
            'pickup_notes'  => $this->pickup_notes,

            // ⬇️ FIELD DELIVERY BARU
            'delivery_location' => $this->delivery_location,
            'delivery_fee'      => (float) ($this->delivery_fee ?? 0),

            // Relasi User (di-load & dicek ketersediaannya)
            'user' => $this->whenLoaded('user', function () {
                if (! $this->user) return null;

                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                ];
            }),

            // Relasi Camera (di-load & dicek ketersediaannya)
            'camera' => $this->whenLoaded('camera', function () {
                if (! $this->camera) return null;

                return [
                    'id'         => $this->camera->id,
                    'name'       => $this->camera->name,
                    'brand'      => $this->camera->brand,
                    'daily_rate' => (float) $this->camera->daily_rate,
                    'image_url'  => $this->camera->image_url ?? ($this->camera->image ? asset('storage/' . $this->camera->image) : null),
                ];
            }),

            // Relasi Payment (di-load & dicek ketersediaannya)
            'payment' => $this->whenLoaded('payment', function () {
                if (! $this->payment) return null;

                return new PaymentResource($this->payment);
            }),

            'created_at' => is_string($this->created_at) ? $this->created_at : $this->created_at?->toIso8601String(),
            'updated_at' => is_string($this->updated_at) ? $this->updated_at : $this->updated_at?->toIso8601String(),
        ];
    }
}
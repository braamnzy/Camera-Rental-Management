<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transformasi resource model Payment ke dalam array JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'rental_id'      => $this->rental_id,
            'order_id'       => $this->order_id,
            'snap_token'     => $this->snap_token,
            'gross_amount'   => (float) $this->gross_amount,
            'payment_type'   => $this->payment_type,
            'payment_status' => $this->payment_status, // pending, settlement, deny, expire, cancel
            'created_at'     => is_string($this->created_at) ? $this->created_at : $this->created_at?->toIso8601String(),
            'updated_at'     => is_string($this->updated_at) ? $this->updated_at : $this->updated_at?->toIso8601String(),
        ];
    }
}
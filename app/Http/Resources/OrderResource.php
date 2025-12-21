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
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user ? $this->user->first_name . ' ' . $this->user->last_name : 'Guest',
                'email' => $this->buyer_email,
            ],
            'event' => [
                'id' => $this->event_id,
                'title' => $this->event ? $this->event->title : 'Unknown Event',
            ],
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'service_fee' => $this->service_fee,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'buyer' => [
                'first_name' => $this->buyer_first_name,
                'last_name' => $this->buyer_last_name,
                'email' => $this->buyer_email,
                'phone' => $this->buyer_phone,
            ],
            'items_count' => $this->items()->count(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'promo_code' => $this->promo_code_id ? [
                'id' => $this->promo_code_id,
                'code' => $this->promoCode ? $this->promoCode->code : null,
            ] : null,
            'completed_at' => $this->completed_at ? $this->completed_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

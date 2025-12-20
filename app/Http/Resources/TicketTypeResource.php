<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketTypeResource extends JsonResource
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
            'event_id' => $this->event_id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'price' => $this->price,
            'currency' => $this->currency,
            'quantity_total' => $this->quantity_total,
            'quantity_sold' => $this->quantity_sold,
            'quantity_reserved' => $this->quantity_reserved,
            'available_quantity' => $this->available_quantity,
            'min_per_order' => $this->min_per_order,
            'max_per_order' => $this->max_per_order,
            'sale_start' => $this->sale_start?->format('Y-m-d H:i:s'),
            'sale_end' => $this->sale_end?->format('Y-m-d H:i:s'),
            'is_visible' => $this->is_visible,
            'requires_approval' => $this->requires_approval,
            'absorb_fees' => $this->absorb_fees,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'event' => $this->whenLoaded('event'),
        ];
    }
}

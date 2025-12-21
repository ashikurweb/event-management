<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'ticket_number' => $this->ticket_number,
            'event_id' => $this->event_id,
            'ticket_type_id' => $this->ticket_type_id,
            'user_id' => $this->user_id,
            'attendee_first_name' => $this->attendee_first_name,
            'attendee_last_name' => $this->attendee_last_name,
            'attendee_full_name' => $this->attendee_full_name,
            'attendee_email' => $this->attendee_email,
            'attendee_phone' => $this->attendee_phone,
            'qr_code' => $this->qr_code,
            'barcode' => $this->barcode,
            'status' => $this->status,
            'checked_in' => $this->checked_in,
            'checked_in_at' => $this->checked_in_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'event' => $this->whenLoaded('event'),
            'ticket_type' => $this->whenLoaded('ticketType'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}

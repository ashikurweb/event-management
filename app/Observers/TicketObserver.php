<?php

namespace App\Observers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketObserver
{
    /**
     * Handle the Ticket "creating" event.
     */
    public function creating(Ticket $ticket): void
    {
        if (empty($ticket->ticket_number)) {
            $ticket->ticket_number = 'TKT-' . strtoupper(Str::random(12));
        }

        if (empty($ticket->qr_code)) {
            $ticket->qr_code = Str::random(32);
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        if (Auth::check()) {
            $ticket->deleted_by = Auth::id();
            $ticket->saveQuietly();
        }
    }
}

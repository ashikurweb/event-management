<?php

namespace App\Observers;

use App\Models\TicketType;
use Illuminate\Support\Facades\Auth;

class TicketTypeObserver
{
    /**
     * Handle the TicketType "deleted" event.
     */
    public function deleted(TicketType $ticketType): void
    {
        if (Auth::check()) {
            $ticketType->deleted_by = Auth::id();
            $ticketType->saveQuietly();
        }
    }
}

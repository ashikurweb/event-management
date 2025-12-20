<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Http\Resources\UserActivityResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $tickets = Ticket::query()
            ->with(['event', 'ticketType', 'user'])
            ->search($request->search)
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('Dashboard/Tickets/Index', [
            'tickets' => TicketResource::collection($tickets),
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Tickets/Create', [
            'events' => \App\Models\Event::select('id', 'title')->get(),
            'ticketTypes' => \App\Models\TicketType::select('id', 'name', 'event_id')->get(),
            'users' => \App\Models\User::select('id', 'first_name', 'last_name')->get(),
        ]);
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'user_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'attendee_first_name' => 'nullable|string|max:100',
            'attendee_last_name' => 'nullable|string|max:100',
            'attendee_email' => 'nullable|email|max:255',
            'attendee_phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,used,cancelled,transferred',
            'checked_in' => 'boolean',
        ]);

        $ticket = Ticket::create($validated);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket created successfully.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['event', 'ticketType', 'user', 'checkedInBy', 'transferredToUser']);

        return Inertia::render('Dashboard/Tickets/Show', [
            'ticket' => new TicketResource($ticket),
        ]);
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit(Ticket $ticket)
    {
        return Inertia::render('Dashboard/Tickets/Edit', [
            'ticket' => new TicketResource($ticket),
            'events' => \App\Models\Event::select('id', 'title')->get(),
            'ticketTypes' => \App\Models\TicketType::select('id', 'name', 'event_id')->get(),
            'users' => \App\Models\User::select('id', 'first_name', 'last_name')->get(),
        ]);
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'user_id' => 'required|exists:users,id',
            'attendee_first_name' => 'nullable|string|max:100',
            'attendee_last_name' => 'nullable|string|max:100',
            'attendee_email' => 'nullable|email|max:255',
            'attendee_phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,used,cancelled,transferred',
            'checked_in' => 'boolean',
        ]);

        if ($request->checked_in && !$ticket->checked_in) {
            $validated['checked_in_at'] = now();
            $validated['checked_in_by'] = Auth::id();
        }

        $ticket->update($validated);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Display activities for a ticket.
     */
    public function activities(Ticket $ticket)
    {
        $activities = \App\Models\UserActivityLog::query()
            ->where('metadata->model_id', $ticket->id)
            ->where('metadata->model_class', get_class($ticket))
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/Tickets/Activities', [
            'ticket' => new TicketResource($ticket),
            'activities' => UserActivityResource::collection($activities),
        ]);
    }

    /**
     * Perform bulk actions on tickets.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|string|in:delete,cancel,check-in',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        switch ($action) {
            case 'delete':
                Ticket::whereIn('id', $ids)->delete();
                $message = count($ids) . ' tickets deleted successfully.';
                break;
            case 'cancel':
                Ticket::whereIn('id', $ids)->update(['status' => 'cancelled']);
                $message = count($ids) . ' tickets cancelled successfully.';
                break;
            case 'check-in':
                Ticket::whereIn('id', $ids)->update([
                    'checked_in' => true,
                    'checked_in_at' => now(),
                    'checked_in_by' => Auth::id()
                ]);
                $message = count($ids) . ' tickets checked in successfully.';
                break;
            default:
                return back()->with('error', 'Invalid action.');
        }

        return back()->with('success', $message);
    }
}

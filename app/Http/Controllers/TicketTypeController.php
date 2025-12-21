<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketTypeResource;
use App\Http\Resources\UserActivityResource;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TicketTypeController extends Controller
{
    /**
     * Display a listing of ticket types.
     */
    public function index(Request $request)
    {
        $ticketTypes = TicketType::query()
            ->with(['event'])
            ->search($request->search)
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('Dashboard/TicketTypes/Index', [
            'ticketTypes' => TicketTypeResource::collection($ticketTypes),
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new ticket type.
     */
    public function create()
    {
        return Inertia::render('Dashboard/TicketTypes/Create', [
            'events' => \App\Models\Event::select('id', 'title')->get(),
        ]);
    }

    /**
     * Store a newly created ticket type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:free,paid,donation',
            'price' => 'required_if:type,paid|numeric|min:0',
            'currency' => 'required|string|max:3',
            'quantity_total' => 'nullable|integer|min:1',
            'min_per_order' => 'integer|min:1',
            'max_per_order' => 'integer|min:1|gte:min_per_order',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after_or_equal:sale_start',
            'is_visible' => 'boolean',
            'requires_approval' => 'boolean',
            'absorb_fees' => 'boolean',
            'display_order' => 'integer',
        ]);

        $ticketType = TicketType::create($validated);

        return redirect()->route('ticket-types.index')
            ->with('success', 'Ticket type created successfully.');
    }

    /**
     * Display the specified ticket type.
     */
    public function show(TicketType $ticketType)
    {
        $ticketType->load(['event']);

        return Inertia::render('Dashboard/TicketTypes/Show', [
            'ticketType' => new TicketTypeResource($ticketType),
        ]);
    }

    /**
     * Show the form for editing the specified ticket type.
     */
    public function edit(TicketType $ticketType)
    {
        return Inertia::render('Dashboard/TicketTypes/Edit', [
            'ticketType' => new TicketTypeResource($ticketType),
            'events' => \App\Models\Event::select('id', 'title')->get(),
        ]);
    }

    /**
     * Update the specified ticket type in storage.
     */
    public function update(Request $request, TicketType $ticketType)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:free,paid,donation',
            'price' => 'required_if:type,paid|numeric|min:0',
            'currency' => 'required|string|max:3',
            'quantity_total' => 'nullable|integer|min:1',
            'min_per_order' => 'integer|min:1',
            'max_per_order' => 'integer|min:1|gte:min_per_order',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after_or_equal:sale_start',
            'is_visible' => 'boolean',
            'requires_approval' => 'boolean',
            'absorb_fees' => 'boolean',
            'display_order' => 'integer',
        ]);

        $ticketType->update($validated);

        return redirect()->route('ticket-types.index')
            ->with('success', 'Ticket type updated successfully.');
    }

    /**
     * Remove the specified ticket type from storage.
     */
    public function destroy(TicketType $ticketType)
    {
        $ticketType->delete();

        return redirect()->route('ticket-types.index')
            ->with('success', 'Ticket type deleted successfully.');
    }

    /**
     * Display activities for a ticket type.
     */
    public function activities(TicketType $ticketType)
    {
        $activities = \App\Models\UserActivityLog::query()
            ->where('metadata->model_id', $ticketType->id)
            ->where('metadata->model_class', get_class($ticketType))
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/TicketTypes/Activities', [
            'ticketType' => new TicketTypeResource($ticketType),
            'activities' => UserActivityResource::collection($activities),
        ]);
    }

    /**
     * Perform bulk actions on ticket types.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|string|in:delete,archive,publish,unpublish',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        switch ($action) {
            case 'delete':
                TicketType::whereIn('id', $ids)->delete();
                $message = count($ids) . ' ticket types deleted successfully.';
                break;
            case 'publish':
                TicketType::whereIn('id', $ids)->update(['is_visible' => true]);
                $message = count($ids) . ' ticket types published successfully.';
                break;
            case 'unpublish':
                TicketType::whereIn('id', $ids)->update(['is_visible' => false]);
                $message = count($ids) . ' ticket types unpublished successfully.';
                break;
            default:
                return back()->with('error', 'Invalid action.');
        }

        return back()->with('success', $message);
    }
}

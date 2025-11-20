<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTag;
use App\Models\Category;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Http\Requests\Event\EventStoreRequest;
use App\Http\Requests\Event\EventUpdateRequest;
use App\Http\Requests\Event\EventSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::with(['organizer', 'category', 'tags'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Dashboard/Events/Index', [
            'events' => $events,
            'filters' => [],
        ]);
    }

    /**
     * Search events.
     */
    public function search(EventSearchRequest $request)
    {
        $query = Event::with(['organizer', 'category', 'tags']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('slug', 'like', '%' . $request->search . '%')
                    ->orWhereHas('organizer', function ($userQuery) use ($request) {
                        $userQuery->where('first_name', 'like', '%' . $request->search . '%')
                            ->orWhere('last_name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($request) {
                        $categoryQuery->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['title', 'start_date', 'end_date', 'created_at', 'updated_at', 'view_count'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $events = $query->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Events/Index', [
            'events' => $events,
            'filters' => $request->only(['search', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizers = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
        
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $tags = EventTag::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Dashboard/Events/Create', [
            'organizers' => $organizers,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventStoreRequest $request)
    {
        $validated = $request->validated();

        // Generate UUID if not provided (handled by model boot)
        // Generate slug if not provided (handled by model boot)
        
        // Set default status if not provided
        if (empty($validated['status'])) {
            $validated['status'] = 'draft';
        }

        // Set default visibility if not provided
        if (empty($validated['visibility'])) {
            $validated['visibility'] = 'public';
        }

        // Handle tags
        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $event = Event::create($validated);

        // Attach tags
        if (!empty($tags)) {
            $event->tags()->attach($tags);
            
            // Update usage count for tags
            EventTag::whereIn('id', $tags)->increment('usage_count');
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'event.created',
            'description' => "Event '{$event->title}' was created",
            'metadata' => [
                'event_id' => $event->id,
                'event_uuid' => $event->uuid,
                'event_title' => $event->title,
                'organizer_id' => $event->organizer_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('events.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Request $request)
    {
        $event->load(['organizer', 'category', 'tags', 'deletedBy']);

        // Get activity logs for this event
        $activities = UserActivityLog::where(function ($query) use ($event) {
                $query->where('action', 'like', 'event.%')
                    ->whereJsonContains('metadata->event_id', $event->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Events/Show', [
            'event' => $event,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for an event.
     */
    public function activities(Event $event, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($event) {
                $query->where('action', 'like', 'event.%')
                    ->whereJsonContains('metadata->event_id', $event->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Events/Show', [
            'event' => $event->load(['organizer', 'category', 'tags', 'deletedBy']),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $organizers = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
        
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $tags = EventTag::orderBy('name')->get(['id', 'name']);

        $event->load(['organizer', 'category', 'tags']);

        return Inertia::render('Dashboard/Events/Edit', [
            'event' => $event,
            'organizers' => $organizers,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventUpdateRequest $request, Event $event)
    {
        $validated = $request->validated();

        // Handle tags
        $tags = $validated['tags'] ?? null;
        unset($validated['tags']);

        // Update slug if title changed and slug is empty
        if (isset($validated['title']) && $event->title !== $validated['title'] && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure uniqueness
            $slug = $validated['slug'];
            $counter = 1;
            while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                $slug = $validated['slug'] . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $event->update($validated);

        // Update tags if provided
        if ($tags !== null) {
            // Decrement usage count for old tags
            $oldTagIds = $event->tags->pluck('id')->toArray();
            EventTag::whereIn('id', $oldTagIds)->decrement('usage_count');
            
            // Sync tags
            $event->tags()->sync($tags);
            
            // Increment usage count for new tags
            EventTag::whereIn('id', $tags)->increment('usage_count');
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'event.updated',
            'description' => "Event '{$event->title}' was updated",
            'metadata' => [
                'event_id' => $event->id,
                'event_uuid' => $event->uuid,
                'event_title' => $event->title,
                'organizer_id' => $event->organizer_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('events.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $eventTitle = $event->title;
        $eventId = $event->id;
        $eventUuid = $event->uuid;
        $organizerId = $event->organizer_id;

        // Decrement usage count for tags
        $tagIds = $event->tags->pluck('id')->toArray();
        if (!empty($tagIds)) {
            EventTag::whereIn('id', $tagIds)->decrement('usage_count');
        }

        // Set deleted_by before soft delete
        $event->deleted_by = Auth::id();
        $event->save();
        $event->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'event.deleted',
            'description' => "Event '{$eventTitle}' was deleted",
            'metadata' => [
                'event_id' => $eventId,
                'event_uuid' => $eventUuid,
                'event_title' => $eventTitle,
                'organizer_id' => $organizerId,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,publish,unpublish,feature,unfeature',
            'ids' => 'required|array',
            'ids.*' => 'exists:events,id',
        ]);

        $events = Event::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'delete':
                // Set deleted_by for all events
                $events->update(['deleted_by' => Auth::id()]);
                $events->delete();
                $message = 'Events deleted successfully.';
                break;
            
            case 'publish':
                $events->update([
                    'status' => 'published',
                    'published_at' => now(),
                ]);
                $message = 'Events published successfully.';
                break;
            
            case 'unpublish':
                $events->update([
                    'status' => 'draft',
                    'published_at' => null,
                ]);
                $message = 'Events unpublished successfully.';
                break;
            
            case 'feature':
                $events->update(['is_featured' => true]);
                $message = 'Events featured successfully.';
                break;
            
            case 'unfeature':
                $events->update(['is_featured' => false]);
                $message = 'Events unfeatured successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}


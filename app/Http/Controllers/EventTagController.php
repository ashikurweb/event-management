<?php

namespace App\Http\Controllers;

use App\Models\EventTag;
use App\Models\UserActivityLog;
use App\Http\Requests\EventTag\EventTagStoreRequest;
use App\Http\Requests\EventTag\EventTagUpdateRequest;
use App\Http\Requests\EventTag\EventTagSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = EventTag::withCount('events')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Dashboard/EventTags/Index', [
            'tags' => $tags,
            'filters' => [],
        ]);
    }

    /**
     * Search event tags.
     */
    public function search(EventTagSearchRequest $request)
    {
        $query = EventTag::withCount('events');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('slug', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'usage_count', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $tags = $query->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/EventTags/Index', [
            'tags' => $tags,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/EventTags/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventTagStoreRequest $request)
    {
        $validated = $request->validated();

        // Generate slug if not provided (handled by model boot)
        $tag = EventTag::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'event_tag.created',
            'description' => "Event tag '{$tag->name}' was created",
            'metadata' => [
                'tag_id' => $tag->id,
                'tag_name' => $tag->name,
                'tag_slug' => $tag->slug,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('event-tags.index')
            ->with('success', 'Event tag created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EventTag $eventTag, Request $request)
    {
        $eventTag->load(['events', 'deletedBy']);

        // Get activity logs for this tag
        $activities = UserActivityLog::where(function ($query) use ($eventTag) {
                $query->where('action', 'like', 'event_tag.%')
                    ->whereJsonContains('metadata->tag_id', $eventTag->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/EventTags/Show', [
            'tag' => $eventTag,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for an event tag.
     */
    public function activities(EventTag $eventTag, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($eventTag) {
                $query->where('action', 'like', 'event_tag.%')
                    ->whereJsonContains('metadata->tag_id', $eventTag->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/EventTags/Show', [
            'tag' => $eventTag->load(['events', 'deletedBy']),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventTag $eventTag)
    {
        return Inertia::render('Dashboard/EventTags/Edit', [
            'tag' => $eventTag,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventTagUpdateRequest $request, EventTag $eventTag)
    {
        $validated = $request->validated();

        // Update slug if name changed and slug is empty
        if (isset($validated['name']) && $eventTag->name !== $validated['name'] && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $slug = $validated['slug'];
            $counter = 1;
            while (EventTag::where('slug', $slug)->where('id', '!=', $eventTag->id)->exists()) {
                $slug = $validated['slug'] . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $eventTag->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'event_tag.updated',
            'description' => "Event tag '{$eventTag->name}' was updated",
            'metadata' => [
                'tag_id' => $eventTag->id,
                'tag_name' => $eventTag->name,
                'tag_slug' => $eventTag->slug,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('event-tags.index')
            ->with('success', 'Event tag updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventTag $eventTag)
    {
        $tagName = $eventTag->name;
        $tagId = $eventTag->id;
        $tagSlug = $eventTag->slug;

        // Set deleted_by before soft delete
        $eventTag->deleted_by = Auth::id();
        $eventTag->save();
        $eventTag->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'event_tag.deleted',
            'description' => "Event tag '{$tagName}' was deleted",
            'metadata' => [
                'tag_id' => $tagId,
                'tag_name' => $tagName,
                'tag_slug' => $tagSlug,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('event-tags.index')
            ->with('success', 'Event tag deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:event_tags,id',
        ]);

        $tags = EventTag::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'delete':
                // Set deleted_by for all tags
                $tags->update(['deleted_by' => Auth::id()]);
                $tags->delete();
                $message = 'Event tags deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}


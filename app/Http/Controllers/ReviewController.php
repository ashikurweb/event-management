<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Event;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Http\Requests\Review\ReviewStoreRequest;
use App\Http\Requests\Review\ReviewUpdateRequest;
use App\Http\Requests\Review\ReviewSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = Review::with(['event', 'user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Transform reviews for frontend
        $reviews->getCollection()->transform(function ($review) {
            return [
                'id' => $review->id,
                'event' => [
                    'id' => $review->event->id,
                    'title' => $review->event->title,
                    'slug' => $review->event->slug,
                ],
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->first_name . ' ' . $review->user->last_name,
                    'email' => $review->user->email,
                    'avatar' => $review->user->avatar,
                ],
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'status' => $review->status,
                'is_verified_attendee' => $review->is_verified_attendee,
                'helpful_count' => $review->helpful_count,
                'reported_count' => $review->reported_count,
                'replies_count' => $review->replies->count(),
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $review->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Get events and users for filters
        $events = Event::orderBy('title')->get(['id', 'title']);
        $users = User::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Reviews/Index', [
            'reviews' => $reviews,
            'events' => $events,
            'users' => $users,
            'filters' => [],
        ]);
    }

    /**
     * Search reviews.
     */
    public function search(ReviewSearchRequest $request)
    {
        $query = Review::with(['event', 'user', 'replies.user']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('comment', 'like', '%' . $request->search . '%')
                    ->orWhereHas('event', function ($eventQuery) use ($request) {
                        $eventQuery->where('title', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('user', function ($userQuery) use ($request) {
                        $userQuery->where('first_name', 'like', '%' . $request->search . '%')
                            ->orWhere('last_name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by verified attendee
        if ($request->has('is_verified_attendee') && $request->is_verified_attendee !== null && $request->is_verified_attendee !== '') {
            $isVerified = $request->is_verified_attendee === '1' || $request->is_verified_attendee === 1 || $request->is_verified_attendee === true;
            $query->where('is_verified_attendee', $isVerified);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['created_at', 'updated_at', 'rating', 'helpful_count'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $reviews = $query->paginate($request->get('per_page', 15));

        // Transform reviews for frontend
        $reviews->getCollection()->transform(function ($review) {
            return [
                'id' => $review->id,
                'event' => [
                    'id' => $review->event->id,
                    'title' => $review->event->title,
                    'slug' => $review->event->slug,
                ],
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->first_name . ' ' . $review->user->last_name,
                    'email' => $review->user->email,
                    'avatar' => $review->user->avatar,
                ],
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'status' => $review->status,
                'is_verified_attendee' => $review->is_verified_attendee,
                'helpful_count' => $review->helpful_count,
                'reported_count' => $review->reported_count,
                'replies_count' => $review->replies->count(),
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $review->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Get events and users for filters
        $events = Event::orderBy('title')->get(['id', 'title']);
        $users = User::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Reviews/Index', [
            'reviews' => $reviews,
            'events' => $events,
            'users' => $users,
            'filters' => $request->only(['search', 'event_id', 'user_id', 'status', 'rating', 'is_verified_attendee', 'date_from', 'date_to', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show(Review $review)
    {
        $review->load(['event', 'user', 'replies.user']);

        return Inertia::render('Dashboard/Reviews/Show', [
            'review' => [
                'id' => $review->id,
                'event' => [
                    'id' => $review->event->id,
                    'title' => $review->event->title,
                    'slug' => $review->event->slug,
                ],
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->first_name . ' ' . $review->user->last_name,
                    'email' => $review->user->email,
                    'avatar' => $review->user->avatar,
                ],
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'status' => $review->status,
                'is_verified_attendee' => $review->is_verified_attendee,
                'helpful_count' => $review->helpful_count,
                'reported_count' => $review->reported_count,
                'replies' => $review->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'user' => [
                            'id' => $reply->user->id,
                            'name' => $reply->user->first_name . ' ' . $reply->user->last_name,
                            'email' => $reply->user->email,
                            'avatar' => $reply->user->avatar,
                        ],
                        'comment' => $reply->comment,
                        'created_at' => $reply->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $reply->updated_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $review->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending'; // Default status

        $review = Review::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review.created',
            'description' => "Review for event '{$review->event->title}' was created",
            'metadata' => [
                'review_id' => $review->id,
                'event_id' => $review->event_id,
                'rating' => $review->rating,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReviewUpdateRequest $request, Review $review)
    {
        $validated = $request->validated();
        $review->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review.updated',
            'description' => "Review #{$review->id} was updated",
            'metadata' => [
                'review_id' => $review->id,
                'event_id' => $review->event_id,
                'status' => $review->status,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $reviewId = $review->id;
        $eventId = $review->event_id;

        // Set deleted_by before soft delete
        $review->deleted_by = Auth::id();
        $review->save();
        $review->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review.deleted',
            'description' => "Review #{$reviewId} was deleted",
            'metadata' => [
                'review_id' => $reviewId,
                'event_id' => $eventId,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    /**
     * Approve a review.
     */
    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review.approved',
            'description' => "Review #{$review->id} was approved",
            'metadata' => [
                'review_id' => $review->id,
                'event_id' => $review->event_id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review approved successfully.');
    }

    /**
     * Reject a review.
     */
    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review.rejected',
            'description' => "Review #{$review->id} was rejected",
            'metadata' => [
                'review_id' => $review->id,
                'event_id' => $review->event_id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review rejected successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id',
        ]);

        $reviews = Review::whereIn('id', $request->ids)->get();
        $action = $request->action;

        foreach ($reviews as $review) {
            if ($action === 'approve') {
                $review->update(['status' => 'approved']);
            } elseif ($action === 'reject') {
                $review->update(['status' => 'rejected']);
            } elseif ($action === 'delete') {
                $review->deleted_by = Auth::id();
                $review->save();
                $review->delete();
            }
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => "review.bulk.{$action}",
            'description' => count($reviews) . " review(s) were {$action}d",
            'metadata' => [
                'count' => count($reviews),
                'action' => $action,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('reviews.index')
            ->with('success', count($reviews) . ' review(s) ' . $action . 'd successfully.');
    }
}


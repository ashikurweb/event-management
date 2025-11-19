<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use App\Models\Event;
use App\Models\Category;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Http\Requests\PromoCode\PromoCodeStoreRequest;
use App\Http\Requests\PromoCode\PromoCodeUpdateRequest;
use App\Http\Requests\PromoCode\PromoCodeSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PromoCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promoCodes = PromoCode::with(['creator', 'deletedBy'])
            ->withCount('usages')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Dashboard/PromoCodes/Index', [
            'promoCodes' => $promoCodes,
            'filters' => [],
        ]);
    }

    /**
     * Search promo codes.
     */
    public function search(PromoCodeSearchRequest $request)
    {
        $query = PromoCode::with(['creator', 'deletedBy'])
            ->withCount('usages');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by discount type
        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }

        // Filter by applicable to
        if ($request->filled('applicable_to')) {
            $query->where('applicable_to', $request->applicable_to);
        }

        // Filter by active status
        if ($request->has('is_active') && $request->is_active !== null && $request->is_active !== '') {
            $isActive = $request->is_active === '1' || $request->is_active === 1 || $request->is_active === true;
            $query->where('is_active', $isActive);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('valid_from', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('valid_until', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['code', 'discount_value', 'current_uses', 'valid_from', 'valid_until', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $promoCodes = $query->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/PromoCodes/Index', [
            'promoCodes' => $promoCodes,
            'filters' => $request->only(['search', 'discount_type', 'applicable_to', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $events = Event::orderBy('title')->get(['id', 'title']);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Dashboard/PromoCodes/Create', [
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PromoCodeStoreRequest $request)
    {
        $validated = $request->validated();

        // Handle events and categories
        $events = $validated['events'] ?? [];
        $categories = $validated['categories'] ?? [];
        unset($validated['events'], $validated['categories']);

        // Set created_by
        $validated['created_by'] = Auth::id();

        // Set default max_uses_per_user if not provided
        if (empty($validated['max_uses_per_user'])) {
            $validated['max_uses_per_user'] = 1;
        }

        // Set default is_active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $promoCode = PromoCode::create($validated);

        // Attach events if applicable
        if ($validated['applicable_to'] === 'specific_events' && !empty($events)) {
            $promoCode->events()->attach($events);
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'promo_code.created',
            'description' => "Promo code '{$promoCode->code}' was created",
            'metadata' => [
                'promo_code_id' => $promoCode->id,
                'promo_code' => $promoCode->code,
                'discount_type' => $promoCode->discount_type,
                'discount_value' => $promoCode->discount_value,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('promo-codes.index')
            ->with('success', 'Promo code created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PromoCode $promoCode, Request $request)
    {
        $promoCode->load(['creator', 'events', 'deletedBy']);

        // Get usage history
        $usages = $promoCode->usages()
            ->with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Get activity logs
        $activities = UserActivityLog::where(function ($query) use ($promoCode) {
                $query->where('action', 'like', 'promo_code.%')
                    ->whereJsonContains('metadata->promo_code_id', $promoCode->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/PromoCodes/Show', [
            'promoCode' => $promoCode,
            'usages' => $usages,
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PromoCode $promoCode)
    {
        $events = Event::orderBy('title')->get(['id', 'title']);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        $promoCode->load(['events']);

        return Inertia::render('Dashboard/PromoCodes/Edit', [
            'promoCode' => $promoCode,
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PromoCodeUpdateRequest $request, PromoCode $promoCode)
    {
        $validated = $request->validated();

        // Handle events
        $events = $validated['events'] ?? null;
        $categories = $validated['categories'] ?? null;
        unset($validated['events'], $validated['categories']);

        $promoCode->update($validated);

        // Update events if applicable
        if ($validated['applicable_to'] === 'specific_events') {
            if ($events !== null) {
                $promoCode->events()->sync($events);
            }
        } else {
            // Remove all event associations if not applicable to specific events
            $promoCode->events()->detach();
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'promo_code.updated',
            'description' => "Promo code '{$promoCode->code}' was updated",
            'metadata' => [
                'promo_code_id' => $promoCode->id,
                'promo_code' => $promoCode->code,
                'discount_type' => $promoCode->discount_type,
                'discount_value' => $promoCode->discount_value,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('promo-codes.index')
            ->with('success', 'Promo code updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PromoCode $promoCode)
    {
        $promoCodeCode = $promoCode->code;
        $promoCodeId = $promoCode->id;

        // Set deleted_by before soft delete
        $promoCode->deleted_by = Auth::id();
        $promoCode->save();
        $promoCode->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'promo_code.deleted',
            'description' => "Promo code '{$promoCodeCode}' was deleted",
            'metadata' => [
                'promo_code_id' => $promoCodeId,
                'promo_code' => $promoCodeCode,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('promo-codes.index')
            ->with('success', 'Promo code deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'ids' => 'required|array',
            'ids.*' => 'exists:promo_codes,id',
        ]);

        $promoCodes = PromoCode::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'delete':
                // Set deleted_by for all promo codes
                $promoCodes->update(['deleted_by' => Auth::id()]);
                $promoCodes->delete();
                $message = 'Promo codes deleted successfully.';
                break;
            
            case 'activate':
                $promoCodes->update(['is_active' => true]);
                $message = 'Promo codes activated successfully.';
                break;
            
            case 'deactivate':
                $promoCodes->update(['is_active' => false]);
                $message = 'Promo codes deactivated successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use App\Models\UserActivityLog;
use App\Http\Requests\Sponsor\SponsorStoreRequest;
use App\Http\Requests\Sponsor\SponsorUpdateRequest;
use App\Http\Requests\Sponsor\SponsorSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\FileHelper;

class SponsorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sponsors = Sponsor::orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Add logo URLs to each sponsor
        $sponsors->getCollection()->transform(function ($sponsor) {
            $sponsor->logo_url = $sponsor->logo ? Storage::disk('public')->url($sponsor->logo) : null;
            return $sponsor;
        });

        return Inertia::render('Dashboard/Sponsors/Index', [
            'sponsors' => $sponsors,
            'filters' => [],
        ]);
    }

    /**
     * Search sponsors.
     */
    public function search(SponsorSearchRequest $request)
    {
        $query = Sponsor::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('website', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by tier
        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }

        // Filter by active status
        if ($request->has('is_active') && $request->is_active !== null && $request->is_active !== '') {
            $isActive = $request->is_active === '1' || $request->is_active === 1 || $request->is_active === true;
            $query->where('is_active', $isActive);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'display_order');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['name', 'tier', 'display_order', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('display_order')->orderBy('created_at', 'desc');
        }

        $sponsors = $query->paginate($request->get('per_page', 15));
        
        // Add logo URLs to each sponsor
        $sponsors->getCollection()->transform(function ($sponsor) {
            $sponsor->logo_url = $sponsor->logo ? Storage::disk('public')->url($sponsor->logo) : null;
            return $sponsor;
        });

        return Inertia::render('Dashboard/Sponsors/Index', [
            'sponsors' => $sponsors,
            'filters' => $request->only(['search', 'tier', 'is_active', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Sponsors/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SponsorStoreRequest $request)
    {
        $validated = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = FileHelper::saveFile($request->file('logo'), 'sponsors', 'public');
            if ($logoPath) {
                $validated['logo'] = $logoPath;
            }
        }

        $sponsor = Sponsor::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'sponsor.created',
            'description' => "Sponsor '{$sponsor->name}' was created",
            'metadata' => [
                'sponsor_id' => $sponsor->id,
                'sponsor_name' => $sponsor->name,
                'sponsor_tier' => $sponsor->tier,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('sponsors.index')
            ->with('success', 'Sponsor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sponsor $sponsor, Request $request)
    {
        $sponsor->load('events', 'deletedBy');
        
        // Add logo URL
        $sponsor->logo_url = $sponsor->logo ? Storage::disk('public')->url($sponsor->logo) : null;

        // Get activity logs for this sponsor
        $activities = UserActivityLog::where(function ($query) use ($sponsor) {
                $query->where('action', 'like', 'sponsor.%')
                    ->whereJsonContains('metadata->sponsor_id', $sponsor->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Sponsors/Show', [
            'sponsor' => $sponsor,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a sponsor.
     */
    public function activities(Sponsor $sponsor, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($sponsor) {
                $query->where('action', 'like', 'sponsor.%')
                    ->whereJsonContains('metadata->sponsor_id', $sponsor->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Sponsors/Show', [
            'sponsor' => $sponsor->load('events', 'deletedBy'),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sponsor $sponsor)
    {
        // Add logo URL
        $sponsor->logo_url = $sponsor->logo ? Storage::disk('public')->url($sponsor->logo) : null;

        return Inertia::render('Dashboard/Sponsors/Edit', [
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SponsorUpdateRequest $request, Sponsor $sponsor)
    {
        $validated = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($sponsor->logo && Storage::disk('public')->exists($sponsor->logo)) {
                Storage::disk('public')->delete($sponsor->logo);
            }
            
            $logoPath = FileHelper::saveFile($request->file('logo'), 'sponsors', 'public');
            if ($logoPath) {
                $validated['logo'] = $logoPath;
            }
        }

        // Handle logo removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            if ($sponsor->logo && Storage::disk('public')->exists($sponsor->logo)) {
                Storage::disk('public')->delete($sponsor->logo);
            }
            $validated['logo'] = null;
        }

        $sponsor->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'sponsor.updated',
            'description' => "Sponsor '{$sponsor->name}' was updated",
            'metadata' => [
                'sponsor_id' => $sponsor->id,
                'sponsor_name' => $sponsor->name,
                'sponsor_tier' => $sponsor->tier,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('sponsors.index')
            ->with('success', 'Sponsor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sponsor $sponsor)
    {
        // Delete logo if exists
        if ($sponsor->logo && Storage::disk('public')->exists($sponsor->logo)) {
            Storage::disk('public')->delete($sponsor->logo);
        }

        $sponsorName = $sponsor->name;
        $sponsorId = $sponsor->id;
        $sponsorTier = $sponsor->tier;

        // Set deleted_by before soft delete
        $sponsor->deleted_by = Auth::id();
        $sponsor->save();
        $sponsor->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'sponsor.deleted',
            'description' => "Sponsor '{$sponsorName}' was deleted",
            'metadata' => [
                'sponsor_id' => $sponsorId,
                'sponsor_name' => $sponsorName,
                'sponsor_tier' => $sponsorTier,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('sponsors.index')
            ->with('success', 'Sponsor deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:sponsors,id',
        ]);

        $sponsors = Sponsor::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'activate':
                $sponsors->update(['is_active' => true]);
                $message = 'Sponsors activated successfully.';
                break;

            case 'deactivate':
                $sponsors->update(['is_active' => false]);
                $message = 'Sponsors deactivated successfully.';
                break;

            case 'delete':
                // Delete logos
                $sponsorsToDelete = $sponsors->get();
                foreach ($sponsorsToDelete as $sponsorModel) {
                    if ($sponsorModel->logo && Storage::disk('public')->exists($sponsorModel->logo)) {
                        Storage::disk('public')->delete($sponsorModel->logo);
                    }
                }

                $sponsors->update(['deleted_by' => Auth::id()]);
                $sponsors->delete();
                $message = 'Sponsors deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}


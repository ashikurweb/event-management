<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\UserActivityLog;
use App\Http\Requests\Venue\VenueStoreRequest;
use App\Http\Requests\Venue\VenueUpdateRequest;
use App\Http\Requests\Venue\VenueSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\FileHelper;

class VenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $venues = Venue::orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Add image URLs to each venue
        $venues->getCollection()->transform(function ($venue) {
            $venue->image_urls = $this->getImageUrls($venue);
            return $venue;
        });

        return Inertia::render('Dashboard/Venues/Index', [
            'venues' => $venues,
            'filters' => [],
        ]);
    }

    /**
     * Search venues.
     */
    public function search(VenueSearchRequest $request)
    {
        $query = Venue::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('slug', 'like', '%' . $request->search . '%')
                    ->orWhere('address', 'like', '%' . $request->search . '%')
                    ->orWhere('city', 'like', '%' . $request->search . '%')
                    ->orWhere('country', 'like', '%' . $request->search . '%')
                    ->orWhere('contact_name', 'like', '%' . $request->search . '%')
                    ->orWhere('contact_email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by verification status
        if ($request->has('is_verified') && $request->is_verified !== null && $request->is_verified !== '') {
            $isVerified = $request->is_verified === '1' || $request->is_verified === 1 || $request->is_verified === true;
            $query->where('is_verified', $isVerified);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->country . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'city', 'country', 'capacity', 'rating', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $venues = $query->paginate($request->get('per_page', 15));
        
        // Add image URLs to each venue
        $venues->getCollection()->transform(function ($venue) {
            $venue->image_urls = $this->getImageUrls($venue);
            return $venue;
        });

        return Inertia::render('Dashboard/Venues/Index', [
            'venues' => $venues,
            'filters' => $request->only(['search', 'is_verified', 'city', 'country', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Venues/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VenueStoreRequest $request)
    {
        $validated = $request->validated();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Venue::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            $folder = 'venues';
            $imagePaths = FileHelper::saveMultipleFiles($request->file('images'), $folder, 'public');
            $validated['images'] = $imagePaths;
        }

        // Handle facilities array
        if (isset($validated['facilities']) && is_array($validated['facilities'])) {
            $validated['facilities'] = array_filter($validated['facilities']);
            if (empty($validated['facilities'])) {
                $validated['facilities'] = null;
            }
        }

        $venue = Venue::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'venue.created',
            'description' => "Venue '{$venue->name}' was created",
            'metadata' => [
                'venue_id' => $venue->id,
                'venue_name' => $venue->name,
                'venue_slug' => $venue->slug,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('venues.index')
            ->with('success', 'Venue created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Venue $venue, Request $request)
    {
        $venue->load('deletedBy');
        
        // Add image URLs
        $venue->image_urls = $this->getImageUrls($venue);

        // Get activity logs for this venue
        $activities = UserActivityLog::where(function ($query) use ($venue) {
                $query->where('action', 'like', 'venue.%')
                    ->whereJsonContains('metadata->venue_id', $venue->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Venues/Show', [
            'venue' => $venue,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a venue.
     */
    public function activities(Venue $venue, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($venue) {
                $query->where('action', 'like', 'venue.%')
                    ->whereJsonContains('metadata->venue_id', $venue->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Venues/Show', [
            'venue' => $venue->load('deletedBy'),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venue $venue)
    {
        // Add image URLs
        $venue->image_urls = $this->getImageUrls($venue);

        return Inertia::render('Dashboard/Venues/Edit', [
            'venue' => $venue,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VenueUpdateRequest $request, Venue $venue)
    {
        $validated = $request->validated();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Venue::where('slug', $validated['slug'])->where('id', '!=', $venue->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Handle images removal
        if ($request->has('remove_images') && is_array($request->remove_images)) {
            $filesToRemove = array_map(function($url) {
                $path = parse_url($url, PHP_URL_PATH);
                return str_replace('/storage/', '', $path);
            }, $request->remove_images);
            
            FileHelper::deleteMultipleFiles($filesToRemove, 'public');
            
            // Update images array
            $currentImages = $venue->images ?? [];
            $validated['images'] = array_values(array_filter($currentImages, function($img) use ($filesToRemove) {
                return !in_array($img, $filesToRemove);
            }));
            
            if (empty($validated['images'])) {
                $validated['images'] = null;
            }
        }
        
        // Handle new images upload
        if ($request->hasFile('images')) {
            $folder = 'venues';
            $newImagePaths = FileHelper::saveMultipleFiles($request->file('images'), $folder, 'public');
            $existingImages = $validated['images'] ?? ($venue->images ?? []);
            $validated['images'] = array_merge($existingImages, $newImagePaths);
        }

        // Handle facilities array
        if (isset($validated['facilities']) && is_array($validated['facilities'])) {
            $validated['facilities'] = array_filter($validated['facilities']);
            if (empty($validated['facilities'])) {
                $validated['facilities'] = null;
            }
        }

        $venue->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'venue.updated',
            'description' => "Venue '{$venue->name}' was updated",
            'metadata' => [
                'venue_id' => $venue->id,
                'venue_name' => $venue->name,
                'venue_slug' => $venue->slug,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('venues.index')
            ->with('success', 'Venue updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venue $venue)
    {
        // Delete images if exists
        if ($venue->images && is_array($venue->images)) {
            FileHelper::deleteMultipleFiles($venue->images, 'public');
        }

        $venueName = $venue->name;
        $venueId = $venue->id;
        $venueSlug = $venue->slug;

        // Set deleted_by before soft delete
        $venue->deleted_by = Auth::id();
        $venue->save();
        $venue->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'venue.deleted',
            'description' => "Venue '{$venueName}' was deleted",
            'metadata' => [
                'venue_id' => $venueId,
                'venue_name' => $venueName,
                'venue_slug' => $venueSlug,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('venues.index')
            ->with('success', 'Venue deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:verify,unverify,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:venues,id',
        ]);

        $venues = Venue::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'verify':
                $venues->update(['is_verified' => true]);
                $message = 'Venues verified successfully.';
                break;

            case 'unverify':
                $venues->update(['is_verified' => false]);
                $message = 'Venues unverified successfully.';
                break;

            case 'delete':
                // Delete images
                $venuesToDelete = $venues->get();
                foreach ($venuesToDelete as $venueModel) {
                    if ($venueModel->images && is_array($venueModel->images)) {
                        FileHelper::deleteMultipleFiles($venueModel->images, 'public');
                    }
                }

                $venues->update(['deleted_by' => Auth::id()]);
                $venues->delete();
                $message = 'Venues deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Get image URLs for a venue
     */
    private function getImageUrls($venue)
    {
        if (!$venue->images || !is_array($venue->images)) {
            return [];
        }

        return array_map(function($imagePath) {
            return Storage::disk('public')->url($imagePath);
        }, $venue->images);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Speaker;
use App\Models\UserActivityLog;
use App\Http\Requests\Speaker\SpeakerStoreRequest;
use App\Http\Requests\Speaker\SpeakerUpdateRequest;
use App\Http\Requests\Speaker\SpeakerSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\FileHelper;

class SpeakerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $speakers = Speaker::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Add photo URLs to each speaker
        $speakers->getCollection()->transform(function ($speaker) {
            $speaker->photo_urls = $speaker->getPhotoUrls();
            return $speaker;
        });

        return Inertia::render('Dashboard/Speakers/Index', [
            'speakers' => $speakers,
            'filters' => [],
        ]);
    }

    /**
     * Search speakers.
     */
    public function search(SpeakerSearchRequest $request)
    {
        $query = Speaker::with('user');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhere('company', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by featured status
        if ($request->has('is_featured') && $request->is_featured !== null && $request->is_featured !== '') {
            $isFeatured = $request->is_featured === '1' || $request->is_featured === 1 || $request->is_featured === true;
            $query->where('is_featured', $isFeatured);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'email', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $speakers = $query->paginate($request->get('per_page', 15));
        
        // Add photo URLs to each speaker
        $speakers->getCollection()->transform(function ($speaker) {
            $speaker->photo_urls = $speaker->getPhotoUrls();
            return $speaker;
        });

        return Inertia::render('Dashboard/Speakers/Index', [
            'speakers' => $speakers,
            'filters' => $request->only(['search', 'is_featured', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = \App\Models\User::orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                ];
            });

        return Inertia::render('Dashboard/Speakers/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SpeakerStoreRequest $request)
    {
        $validated = $request->validated();

        // Handle multiple photos upload using FileHelper
        if ($request->hasFile('photos')) {
            $photos = FileHelper::saveMultipleFiles($request->file('photos'), 'speakers');
            $validated['photos'] = $photos;
        }

        // Handle empty arrays - convert to null
        if (isset($validated['social_links']) && empty($validated['social_links'])) {
            $validated['social_links'] = null;
        }
        if (isset($validated['specialties']) && empty($validated['specialties'])) {
            $validated['specialties'] = null;
        }

        $speaker = Speaker::create($validated);

        // Handle multiple photos upload using FileHelper
        if ($request->hasFile('photos')) {
            $folder = 'speakers/' . $speaker->id;
            FileHelper::saveMultipleFiles($request->file('photos'), $folder, 'public');
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'speaker.created',
            'description' => "Speaker '{$speaker->name}' was created",
            'metadata' => [
                'speaker_id' => $speaker->id,
                'speaker_name' => $speaker->name,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('speakers.index')
            ->with('success', 'Speaker created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Speaker $speaker, Request $request)
    {
        $speaker->load('user', 'events');
        
        // Get photo URLs from folder
        $speaker->photo_urls = $speaker->getPhotoUrls();

        // Get activity logs for this speaker
        $activities = UserActivityLog::where(function ($query) use ($speaker) {
                $query->where('action', 'like', 'speaker.%')
                    ->whereJsonContains('metadata->speaker_id', $speaker->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Speakers/Show', [
            'speaker' => $speaker,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a speaker.
     */
    public function activities(Speaker $speaker, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($speaker) {
                $query->where('action', 'like', 'speaker.%')
                    ->whereJsonContains('metadata->speaker_id', $speaker->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Speakers/Show', [
            'speaker' => $speaker->load('user', 'events'),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Speaker $speaker)
    {
        $users = \App\Models\User::orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                ];
            });

        // Get photo URLs from folder
        $speaker->photo_urls = $speaker->getPhotoUrls();
        $speaker->photo_paths = $speaker->getPhotoPaths();

        return Inertia::render('Dashboard/Speakers/Edit', [
            'speaker' => $speaker,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SpeakerUpdateRequest $request, Speaker $speaker)
    {
        $validated = $request->validated();
        
        $folder = 'speakers/' . $speaker->id;

        // Handle photos removal using FileHelper
        if ($request->has('remove_photos') && is_array($request->remove_photos)) {
            // remove_photos contains file URLs from frontend
            // Extract paths from URLs (e.g., /storage/speakers/1/file.jpg -> speakers/1/file.jpg)
            $filesToRemove = array_map(function($url) {
                $path = parse_url($url, PHP_URL_PATH);
                return str_replace('/storage/', '', $path);
            }, $request->remove_photos);
            
            FileHelper::deleteMultipleFiles($filesToRemove, 'public');
        }
        
        // Handle new photos upload using FileHelper
        if ($request->hasFile('photos')) {
            FileHelper::saveMultipleFiles($request->file('photos'), $folder, 'public');
        }

        // Handle empty arrays - convert to null
        if (isset($validated['social_links']) && empty($validated['social_links'])) {
            $validated['social_links'] = null;
        }
        if (isset($validated['specialties']) && empty($validated['specialties'])) {
            $validated['specialties'] = null;
        }

        $speaker->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'speaker.updated',
            'description' => "Speaker '{$speaker->name}' was updated",
            'metadata' => [
                'speaker_id' => $speaker->id,
                'speaker_name' => $speaker->name,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('speakers.index')
            ->with('success', 'Speaker updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Speaker $speaker)
    {
        // Delete all photos from folder using FileHelper
        $photoPaths = $speaker->getPhotoPaths();
        if (!empty($photoPaths)) {
            FileHelper::deleteMultipleFiles($photoPaths, 'public');
        }
        
        // Delete the folder if empty
        $folderPath = 'speakers/' . $speaker->id;
        if (Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->deleteDirectory($folderPath);
        }

        $speakerName = $speaker->name;
        $speakerId = $speaker->id;

        // Set deleted_by before soft delete
        $speaker->deleted_by = Auth::id();
        $speaker->save();
        $speaker->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'speaker.deleted',
            'description' => "Speaker '{$speakerName}' was deleted",
            'metadata' => [
                'speaker_id' => $speakerId,
                'speaker_name' => $speakerName,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('speakers.index')
            ->with('success', 'Speaker deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:feature,unfeature,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:speakers,id',
        ]);

        $speakers = Speaker::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'feature':
                $speakers->update(['is_featured' => true]);
                $message = 'Speakers featured successfully.';
                break;

            case 'unfeature':
                $speakers->update(['is_featured' => false]);
                $message = 'Speakers unfeatured successfully.';
                break;

            case 'delete':
                // Delete photos using FileHelper
                $speakersToDelete = $speakers->get();
                foreach ($speakersToDelete as $speakerModel) {
                    $photoPaths = $speakerModel->getPhotoPaths();
                    if (!empty($photoPaths)) {
                        FileHelper::deleteMultipleFiles($photoPaths, 'public');
                    }
                    
                    // Delete folder
                    $folderPath = 'speakers/' . $speakerModel->id;
                    if (Storage::disk('public')->exists($folderPath)) {
                        Storage::disk('public')->deleteDirectory($folderPath);
                    }
                }

                $speakers->update(['deleted_by' => Auth::id()]);
                $speakers->delete();
                $message = 'Speakers deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}


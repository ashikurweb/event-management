<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Event;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Http\Requests\Certificate\CertificateStoreRequest;
use App\Http\Requests\Certificate\CertificateUpdateRequest;
use App\Http\Requests\Certificate\CertificateSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $certificates = Certificate::with(['event', 'user', 'deletedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get events for filter dropdown
        $events = Event::orderBy('title')->get(['id', 'title']);
        
        // Get users for filter dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Certificates/Index', [
            'certificates' => $certificates,
            'events' => $events,
            'users' => $users,
            'filters' => [],
        ]);
    }

    /**
     * Search certificates.
     */
    public function search(CertificateSearchRequest $request)
    {
        $query = Certificate::with(['event', 'user', 'deletedBy']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('certificate_number', 'like', '%' . $request->search . '%')
                    ->orWhere('verification_code', 'like', '%' . $request->search . '%')
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

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('issued_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issued_at', '<=', $request->date_to);
        }

        // Filter by downloaded status
        if ($request->has('is_downloaded') && $request->is_downloaded !== null && $request->is_downloaded !== '') {
            $isDownloaded = $request->is_downloaded === '1' || $request->is_downloaded === 1 || $request->is_downloaded === true;
            if ($isDownloaded) {
                $query->whereNotNull('downloaded_at');
            } else {
                $query->whereNull('downloaded_at');
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['certificate_number', 'issued_at', 'downloaded_at', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $certificates = $query->paginate($request->get('per_page', 15));

        // Get events for filter dropdown
        $events = Event::orderBy('title')->get(['id', 'title']);
        
        // Get users for filter dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Certificates/Index', [
            'certificates' => $certificates,
            'events' => $events,
            'users' => $users,
            'filters' => $request->only(['search', 'event_id', 'user_id', 'is_downloaded']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $events = Event::orderBy('title')->get(['id', 'title']);
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Certificates/Create', [
            'events' => $events,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CertificateStoreRequest $request)
    {
        $validated = $request->validated();

        // Generate certificate number if not provided
        if (empty($validated['certificate_number'])) {
            $validated['certificate_number'] = 'CERT-' . strtoupper(Str::random(12));
            
            // Ensure uniqueness
            while (Certificate::where('certificate_number', $validated['certificate_number'])->exists()) {
                $validated['certificate_number'] = 'CERT-' . strtoupper(Str::random(12));
            }
        }

        // Generate verification code if not provided
        if (empty($validated['verification_code'])) {
            $validated['verification_code'] = Str::random(32);
            
            // Ensure uniqueness
            while (Certificate::where('verification_code', $validated['verification_code'])->exists()) {
                $validated['verification_code'] = Str::random(32);
            }
        }

        // Set issued_at if not provided
        if (empty($validated['issued_at'])) {
            $validated['issued_at'] = now();
        }

        $certificate = Certificate::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'certificate.created',
            'description' => "Certificate '{$certificate->certificate_number}' was created",
            'metadata' => [
                'certificate_id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'event_id' => $certificate->event_id,
                'user_id' => $certificate->user_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Certificate created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Certificate $certificate, Request $request)
    {
        $certificate->load(['event', 'user', 'deletedBy']);

        // Get activity logs for this certificate
        $activities = UserActivityLog::where(function ($query) use ($certificate) {
                $query->where('action', 'like', 'certificate.%')
                    ->whereJsonContains('metadata->certificate_id', $certificate->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Certificates/Show', [
            'certificate' => $certificate,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a certificate.
     */
    public function activities(Certificate $certificate, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($certificate) {
                $query->where('action', 'like', 'certificate.%')
                    ->whereJsonContains('metadata->certificate_id', $certificate->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Certificates/Show', [
            'certificate' => $certificate->load(['event', 'user', 'deletedBy']),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificate $certificate)
    {
        $events = Event::orderBy('title')->get(['id', 'title']);
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Certificates/Edit', [
            'certificate' => $certificate->load(['event', 'user']),
            'events' => $events,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CertificateUpdateRequest $request, Certificate $certificate)
    {
        $validated = $request->validated();

        // Generate certificate number if not provided
        if (empty($validated['certificate_number'])) {
            $validated['certificate_number'] = 'CERT-' . strtoupper(Str::random(12));
            
            // Ensure uniqueness
            while (Certificate::where('certificate_number', $validated['certificate_number'])
                ->where('id', '!=', $certificate->id)
                ->exists()) {
                $validated['certificate_number'] = 'CERT-' . strtoupper(Str::random(12));
            }
        }

        // Generate verification code if not provided
        if (empty($validated['verification_code'])) {
            $validated['verification_code'] = Str::random(32);
            
            // Ensure uniqueness
            while (Certificate::where('verification_code', $validated['verification_code'])
                ->where('id', '!=', $certificate->id)
                ->exists()) {
                $validated['verification_code'] = Str::random(32);
            }
        }

        $certificate->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'certificate.updated',
            'description' => "Certificate '{$certificate->certificate_number}' was updated",
            'metadata' => [
                'certificate_id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'event_id' => $certificate->event_id,
                'user_id' => $certificate->user_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Certificate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        $certificateNumber = $certificate->certificate_number;
        $certificateId = $certificate->id;
        $eventId = $certificate->event_id;
        $userId = $certificate->user_id;

        // Set deleted_by before soft delete
        $certificate->deleted_by = Auth::id();
        $certificate->save();
        $certificate->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'certificate.deleted',
            'description' => "Certificate '{$certificateNumber}' was deleted",
            'metadata' => [
                'certificate_id' => $certificateId,
                'certificate_number' => $certificateNumber,
                'event_id' => $eventId,
                'user_id' => $userId,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Certificate deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:certificates,id',
        ]);

        $certificates = Certificate::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'delete':
                // Set deleted_by for all certificates
                $certificates->update(['deleted_by' => Auth::id()]);
                $certificates->delete();
                $message = 'Certificates deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Download certificate
     */
    public function download(Certificate $certificate)
    {
        // Mark as downloaded
        if (!$certificate->downloaded_at) {
            $certificate->update(['downloaded_at' => now()]);
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'certificate.downloaded',
            'description' => "Certificate '{$certificate->certificate_number}' was downloaded",
            'metadata' => [
                'certificate_id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // TODO: Return file download response
        // For now, return success message
        return back()->with('success', 'Certificate download initiated.');
    }
}


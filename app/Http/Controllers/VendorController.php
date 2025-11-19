<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\UserActivityLog;
use App\Http\Requests\Vendor\VendorStoreRequest;
use App\Http\Requests\Vendor\VendorUpdateRequest;
use App\Http\Requests\Vendor\VendorSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\FileHelper;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = Vendor::orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Add logo URLs to each vendor
        $vendors->getCollection()->transform(function ($vendor) {
            $vendor->logo_url = $vendor->logo ? Storage::disk('public')->url($vendor->logo) : null;
            return $vendor;
        });

        return Inertia::render('Dashboard/Vendors/Index', [
            'vendors' => $vendors,
            'filters' => [],
        ]);
    }

    /**
     * Search vendors.
     */
    public function search(VendorSearchRequest $request)
    {
        $query = Vendor::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('company', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhere('category', 'like', '%' . $request->search . '%');
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

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'company', 'email', 'rating', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $vendors = $query->paginate($request->get('per_page', 15));
        
        // Add logo URLs to each vendor
        $vendors->getCollection()->transform(function ($vendor) {
            $vendor->logo_url = $vendor->logo ? Storage::disk('public')->url($vendor->logo) : null;
            return $vendor;
        });

        return Inertia::render('Dashboard/Vendors/Index', [
            'vendors' => $vendors,
            'filters' => $request->only(['search', 'is_verified', 'category', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Vendors/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VendorStoreRequest $request)
    {
        $validated = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = FileHelper::saveFile($request->file('logo'), 'vendors', 'public');
            if ($logoPath) {
                $validated['logo'] = $logoPath;
            }
        }

        $vendor = Vendor::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'vendor.created',
            'description' => "Vendor '{$vendor->name}' was created",
            'metadata' => [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->name,
                'vendor_company' => $vendor->company,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor, Request $request)
    {
        $vendor->load('events', 'deletedBy');
        
        // Add logo URL
        $vendor->logo_url = $vendor->logo ? Storage::disk('public')->url($vendor->logo) : null;

        // Get activity logs for this vendor
        $activities = UserActivityLog::where(function ($query) use ($vendor) {
                $query->where('action', 'like', 'vendor.%')
                    ->whereJsonContains('metadata->vendor_id', $vendor->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Vendors/Show', [
            'vendor' => $vendor,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a vendor.
     */
    public function activities(Vendor $vendor, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($vendor) {
                $query->where('action', 'like', 'vendor.%')
                    ->whereJsonContains('metadata->vendor_id', $vendor->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Vendors/Show', [
            'vendor' => $vendor->load('events', 'deletedBy'),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
        // Add logo URL
        $vendor->logo_url = $vendor->logo ? Storage::disk('public')->url($vendor->logo) : null;

        return Inertia::render('Dashboard/Vendors/Edit', [
            'vendor' => $vendor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VendorUpdateRequest $request, Vendor $vendor)
    {
        $validated = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
                Storage::disk('public')->delete($vendor->logo);
            }
            
            $logoPath = FileHelper::saveFile($request->file('logo'), 'vendors', 'public');
            if ($logoPath) {
                $validated['logo'] = $logoPath;
            }
        }

        // Handle logo removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
                Storage::disk('public')->delete($vendor->logo);
            }
            $validated['logo'] = null;
        }

        $vendor->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'vendor.updated',
            'description' => "Vendor '{$vendor->name}' was updated",
            'metadata' => [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->name,
                'vendor_company' => $vendor->company,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        // Delete logo if exists
        if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
            Storage::disk('public')->delete($vendor->logo);
        }

        $vendorName = $vendor->name;
        $vendorId = $vendor->id;
        $vendorCompany = $vendor->company;

        // Set deleted_by before soft delete
        $vendor->deleted_by = Auth::id();
        $vendor->save();
        $vendor->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'vendor.deleted',
            'description' => "Vendor '{$vendorName}' was deleted",
            'metadata' => [
                'vendor_id' => $vendorId,
                'vendor_name' => $vendorName,
                'vendor_company' => $vendorCompany,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:verify,unverify,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:vendors,id',
        ]);

        $vendors = Vendor::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'verify':
                $vendors->update(['is_verified' => true]);
                $message = 'Vendors verified successfully.';
                break;

            case 'unverify':
                $vendors->update(['is_verified' => false]);
                $message = 'Vendors unverified successfully.';
                break;

            case 'delete':
                // Delete logos
                $vendorsToDelete = $vendors->get();
                foreach ($vendorsToDelete as $vendorModel) {
                    if ($vendorModel->logo && Storage::disk('public')->exists($vendorModel->logo)) {
                        Storage::disk('public')->delete($vendorModel->logo);
                    }
                }

                $vendors->update(['deleted_by' => Auth::id()]);
                $vendors->delete();
                $message = 'Vendors deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}


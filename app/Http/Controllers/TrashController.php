<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Team;
use App\Models\Event;
use App\Models\Venue;
use App\Models\Sponsor;
use App\Models\Speaker;
use App\Models\Vendor;
use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrashController extends Controller
{
    /**
     * Display all trashed items.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 15);

        $trashedItems = collect();

        // Get trashed items from different models
        if ($type === 'all' || $type === 'categories') {
            $categories = Category::onlyTrashed()
                ->with('deletedBy')
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'category',
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deletedBy ? [
                            'id' => $item->deletedBy->id,
                            'name' => $item->deletedBy->first_name . ' ' . $item->deletedBy->last_name,
                            'email' => $item->deletedBy->email,
                        ] : null,
                    ];
                });
            $trashedItems = $trashedItems->merge($categories);
        }

        if ($type === 'all' || $type === 'teams') {
            $teams = Team::onlyTrashed()
                ->with('deletedBy')
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'team',
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deletedBy ? [
                            'id' => $item->deletedBy->id,
                            'name' => $item->deletedBy->first_name . ' ' . $item->deletedBy->last_name,
                            'email' => $item->deletedBy->email,
                        ] : null,
                    ];
                });
            $trashedItems = $trashedItems->merge($teams);
        }

        if ($type === 'all' || $type === 'events') {
            $events = Event::onlyTrashed()
                ->with('deletedBy')
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'event',
                        'name' => $item->title,
                        'slug' => $item->slug,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deletedBy ? [
                            'id' => $item->deletedBy->id,
                            'name' => $item->deletedBy->first_name . ' ' . $item->deletedBy->last_name,
                            'email' => $item->deletedBy->email,
                        ] : null,
                    ];
                });
            $trashedItems = $trashedItems->merge($events);
        }

        if ($type === 'all' || $type === 'venues') {
            $venues = Venue::onlyTrashed()
                ->with('deletedBy')
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'venue',
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deletedBy ? [
                            'id' => $item->deletedBy->id,
                            'name' => $item->deletedBy->first_name . ' ' . $item->deletedBy->last_name,
                            'email' => $item->deletedBy->email,
                        ] : null,
                    ];
                });
            $trashedItems = $trashedItems->merge($venues);
        }

        if ($type === 'all' || $type === 'sponsors') {
            $sponsors = Sponsor::onlyTrashed()
                ->with('deletedBy')
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'sponsor',
                        'name' => $item->name,
                        'slug' => null,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deletedBy ? [
                            'id' => $item->deletedBy->id,
                            'name' => $item->deletedBy->first_name . ' ' . $item->deletedBy->last_name,
                            'email' => $item->deletedBy->email,
                        ] : null,
                    ];
                });
            $trashedItems = $trashedItems->merge($sponsors);
        }

        if ($type === 'all' || $type === 'speakers') {
            $speakers = Speaker::onlyTrashed()
                ->with('deletedBy')
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'speaker',
                        'name' => $item->name,
                        'slug' => null,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deletedBy ? [
                            'id' => $item->deletedBy->id,
                            'name' => $item->deletedBy->first_name . ' ' . $item->deletedBy->last_name,
                            'email' => $item->deletedBy->email,
                        ] : null,
                    ];
                });
            $trashedItems = $trashedItems->merge($speakers);
        }

        if ($type === 'all' || $type === 'vendors') {
            $vendors = Vendor::onlyTrashed()
                ->with('deletedBy')
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'vendor',
                        'name' => $item->name,
                        'slug' => null,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deletedBy ? [
                            'id' => $item->deletedBy->id,
                            'name' => $item->deletedBy->first_name . ' ' . $item->deletedBy->last_name,
                            'email' => $item->deletedBy->email,
                        ] : null,
                    ];
                });
            $trashedItems = $trashedItems->merge($vendors);
        }

        if ($type === 'all' || $type === 'pages') {
            $pages = Page::onlyTrashed()
                ->with('deletedBy')
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'page',
                        'name' => $item->title,
                        'slug' => $item->slug,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deletedBy ? [
                            'id' => $item->deletedBy->id,
                            'name' => $item->deletedBy->first_name . ' ' . $item->deletedBy->last_name,
                            'email' => $item->deletedBy->email,
                        ] : null,
                    ];
                });
            $trashedItems = $trashedItems->merge($pages);
        }

        // Sort by deleted_at descending
        $trashedItems = $trashedItems->sortByDesc('deleted_at')->values();

        // Paginate manually
        $currentPage = $request->get('page', 1);
        $items = $trashedItems->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $trashedItems->count();

        return Inertia::render('Dashboard/Settings/RecycleBin', [
            'items' => $items,
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
            'filters' => [
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Restore a trashed item.
     */
    public function restore(Request $request, $type, $id)
    {
        $model = $this->getModel($type);
        
        if (!$model) {
            return back()->withErrors(['error' => 'Invalid item type.']);
        }

        $item = $model::onlyTrashed()->findOrFail($id);
        $item->restore();
        $item->update(['deleted_by' => null]);

        return back()->with('success', ucfirst($type) . ' restored successfully.');
    }

    /**
     * Permanently delete an item.
     */
    public function forceDelete(Request $request, $type, $id)
    {
        $model = $this->getModel($type);
        
        if (!$model) {
            return back()->withErrors(['error' => 'Invalid item type.']);
        }

        $item = $model::onlyTrashed()->findOrFail($id);
        $item->forceDelete();

        return back()->with('success', ucfirst($type) . ' permanently deleted.');
    }

    /**
     * Restore multiple items.
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.type' => 'required|string',
            'items.*.id' => 'required|integer',
        ]);

        foreach ($request->items as $itemData) {
            $model = $this->getModel($itemData['type']);
            if ($model) {
                $item = $model::onlyTrashed()->find($itemData['id']);
                if ($item) {
                    $item->restore();
                    $item->update(['deleted_by' => null]);
                }
            }
        }

        return back()->with('success', 'Items restored successfully.');
    }

    /**
     * Permanently delete multiple items.
     */
    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.type' => 'required|string',
            'items.*.id' => 'required|integer',
        ]);

        foreach ($request->items as $itemData) {
            $model = $this->getModel($itemData['type']);
            if ($model) {
                $item = $model::onlyTrashed()->find($itemData['id']);
                if ($item) {
                    $item->forceDelete();
                }
            }
        }

        return back()->with('success', 'Items permanently deleted.');
    }

    /**
     * Get model class by type.
     */
    private function getModel($type)
    {
        $models = [
            'category' => Category::class,
            'team' => Team::class,
            'event' => Event::class,
            'venue' => Venue::class,
            'sponsor' => Sponsor::class,
            'speaker' => Speaker::class,
            'vendor' => Vendor::class,
            'page' => Page::class,
        ];

        return $models[$type] ?? null;
    }
}


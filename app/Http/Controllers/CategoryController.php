<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserActivityLog;
use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Http\Requests\Category\CategorySearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('parent', 'children')
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(15);

        // Get all categories for parent dropdown
        $allCategories = Category::orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return Inertia::render('Dashboard/Categories/Index', [
            'categories' => $categories,
            'allCategories' => $allCategories,
            'filters' => [],
        ]);
    }

    /**
     * Search categories.
     */
    public function search(CategorySearchRequest $request)
    {
        $query = Category::with('parent', 'children');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('slug', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->has('is_active') && $request->is_active !== null && $request->is_active !== '') {
            $isActive = $request->is_active === '1' || $request->is_active === 1 || $request->is_active === true;
            $query->where('is_active', $isActive);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'display_order');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['name', 'slug', 'display_order', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('display_order')->orderBy('name');
        }

        $categories = $query->paginate($request->get('per_page', 15));

        // Get all categories for parent dropdown
        $allCategories = Category::orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return Inertia::render('Dashboard/Categories/Index', [
            'categories' => $categories,
            'allCategories' => $allCategories,
            'filters' => $request->only(['search', 'parent_id', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Dashboard/Categories/Create', [
            'parentCategories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        $validated = $request->validated();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $category = Category::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'category.created',
            'description' => "Category '{$category->name}' was created",
            'metadata' => [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'category_slug' => $category->slug,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category, Request $request)
    {
        $category->load('parent', 'children', 'events');

        // Get activity logs for this category
        $activities = UserActivityLog::where(function ($query) use ($category) {
                $query->where('action', 'like', 'category.%')
                    ->whereJsonContains('metadata->category_id', $category->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Categories/Show', [
            'category' => $category,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a category.
     */
    public function activities(Category $category, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($category) {
                $query->where('action', 'like', 'category.%')
                    ->whereJsonContains('metadata->category_id', $category->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Categories/Show', [
            'category' => $category->load('parent', 'children', 'events'),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Dashboard/Categories/Edit', [
            'category' => $category,
            'parentCategories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $validated = $request->validated();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $category->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'category.updated',
            'description' => "Category '{$category->name}' was updated",
            'metadata' => [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'category_slug' => $category->slug,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has children
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with child categories.']);
        }

        // Check if category has events
        if ($category->events()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with associated events.']);
        }

        $categoryName = $category->name;
        $categoryId = $category->id;
        $categorySlug = $category->slug;

        $category->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'category.deleted',
            'description' => "Category '{$categoryName}' was deleted",
            'metadata' => [
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        $categories = Category::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'activate':
                $categories->update(['is_active' => true]);
                $message = 'Categories activated successfully.';
                break;

            case 'deactivate':
                $categories->update(['is_active' => false]);
                $message = 'Categories deactivated successfully.';
                break;

            case 'delete':
                // Check for dependencies
                $categoriesWithChildren = $categories->whereHas('children')->count();
                $categoriesWithEvents = $categories->whereHas('events')->count();

                if ($categoriesWithChildren > 0 || $categoriesWithEvents > 0) {
                    return back()->withErrors([
                        'error' => 'Some categories cannot be deleted due to dependencies.'
                    ]);
                }

                $categories->delete();
                $message = 'Categories deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}


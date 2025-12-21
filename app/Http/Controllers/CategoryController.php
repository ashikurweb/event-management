<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserActivityLog;
use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Http\Requests\Category\CategorySearchRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\UserActivityResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): Response
    {
        return $this->renderIndex(Category::paginate(15));
    }

    /**
     * Search and filter categories.
     */
    public function search(CategorySearchRequest $request): Response
    {
        $categories = Category::with(['parent', 'children'])
            ->search($request->search)
            ->dateBetween($request->date_from, $request->date_to)
            ->orderBy($request->get('sort_by', 'display_order'), $request->get('sort_order', 'asc'))
            ->paginate($request->get('per_page', 15));

        return $this->renderIndex($categories, $request->only(['search', 'date_from', 'date_to', 'sort_by', 'sort_order']));
    }

    /**
     * Show category creation form.
     */
    public function create(): Response
    {
        return Inertia::render('Dashboard/Categories/Create', [
            'parentCategories' => CategoryResource::collection(Category::whereNull('parent_id')->get()),
        ]);
    }

    /**
     * Store a new category.
     */
    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display category details and activity logs.
     */
    public function show(Category $category, Request $request): Response
    {
        $activities = UserActivityLog::where('action', 'like', 'category.%')
            ->where(function($q) use ($category) {
                $q->whereJsonContains('metadata->model_id', $category->id)
                  ->orWhereJsonContains('metadata->category_id', $category->id);
            })
            ->with('user')
            ->latest()
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Categories/Show', [
            'category' => new CategoryResource($category->load(['parent', 'children', 'events'])),
            'activities' => UserActivityResource::collection($activities),
        ]);
    }

    /**
     * Show category edit form.
     */
    public function edit(Category $category): Response
    {
        $parents = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();

        return Inertia::render('Dashboard/Categories/Edit', [
            'category' => new CategoryResource($category),
            'parentCategories' => CategoryResource::collection($parents),
        ]);
    }

    /**
     * Update an existing category.
     */
    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists() || $category->events()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete category with active dependencies.']);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }

    /**
     * Perform bulk operations.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array|exists:categories,id',
        ]);

        $categories = Category::whereIn('id', $request->ids);

        match ($request->action) {
            'activate' => $categories->update(['is_active' => true]),
            'deactivate' => $categories->update(['is_active' => false]),
            'delete' => $categories->delete(), // Note: Soft deletes used
        };

        return back()->with('success', 'Bulk action completed successfully.');
    }

    /**
     * Helper to render the index view with standardized props.
     */
    protected function renderIndex($categories, array $filters = []): Response
    {
        return Inertia::render('Dashboard/Categories/Index', [
            'categories' => CategoryResource::collection($categories),
            'allCategories' => CategoryResource::collection(Category::all()),
            'filters' => $filters,
        ]);
    }
}

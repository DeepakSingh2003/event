<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ActivityLogService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly SlugService $slugService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $categories = Category::query()
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->withCount('events')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z][A-Za-z0-9]*$/'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = Category::create([
            ...$data,
            'icon' => $data['icon'] ?? null,
            'slug' => $this->slugService->generate(Category::class, $data['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->activityLogService->log($request->user(), 'category.created', $category, 'Category created.');

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z][A-Za-z0-9]*$/'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            ...$data,
            'icon' => $data['icon'] ?? null,
            'slug' => $this->slugService->generate(Category::class, $data['name'], $category->id),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->activityLogService->log($request->user(), 'category.updated', $category, 'Category updated.');

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $category->delete();

        $this->activityLogService->log($request->user(), 'category.deleted', $category, 'Category deleted.');

        return back()->with('success', 'Category deleted successfully.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\ActivityLogService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function __construct(
        private readonly SlugService $slugService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $tags = Tag::query()
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->withCount('events')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tag = Tag::create([
            'name' => $data['name'],
            'slug' => $this->slugService->generate(Tag::class, $data['name']),
        ]);

        $this->activityLogService->log($request->user(), 'tag.created', $tag, 'Tag created.');

        return back()->with('success', 'Tag created successfully.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tag->update([
            'name' => $data['name'],
            'slug' => $this->slugService->generate(Tag::class, $data['name'], $tag->id),
        ]);

        $this->activityLogService->log($request->user(), 'tag.updated', $tag, 'Tag updated.');

        return back()->with('success', 'Tag updated successfully.');
    }

    public function destroy(Request $request, Tag $tag): RedirectResponse
    {
        $tag->delete();

        $this->activityLogService->log($request->user(), 'tag.deleted', $tag, 'Tag deleted.');

        return back()->with('success', 'Tag deleted successfully.');
    }
}

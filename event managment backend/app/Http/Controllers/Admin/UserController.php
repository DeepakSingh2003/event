<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly RecommendationService $recommendationService,
    ) {
    }

    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount('bookings')
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->string('role')->value(), fn ($query, $role) => $query->where('role', $role))
            ->when($request->filled('blocked'), fn ($query) => $query->where('is_blocked', $request->boolean('blocked')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load([
            'bookings.event.eventCategory',
            'bookings.show.venue.cityRecord',
            'bookings.items.showSeat.seatType',
            'activityLogs',
        ]);
        $recommendations = $this->recommendationService->forUser($user);

        return view('admin.users.show', compact('user', 'recommendations'));
    }

    public function toggleBlock(Request $request, User $user): RedirectResponse
    {
        $user->update([
            'is_blocked' => ! $user->is_blocked,
            'blocked_at' => $user->is_blocked ? null : now(),
        ]);

        $this->activityLogService->log($request->user(), 'user.block_toggled', $user, 'User access changed.', [
            'is_blocked' => $user->fresh()->is_blocked,
        ]);

        return back()->with('success', 'User status updated successfully.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:admin,manager,user'],
        ]);

        $user->update($data);

        $this->activityLogService->log($request->user(), 'user.role_updated', $user, 'User role updated.', $data);

        return back()->with('success', 'User role updated successfully.');
    }
}

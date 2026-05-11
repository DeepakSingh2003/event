<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request): View
    {
        $coupons = Coupon::query()
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $coupon = Coupon::create($this->validatedData($request));

        $this->activityLogService->log($request->user(), 'coupon.created', $coupon, 'Coupon created.');

        return back()->with('success', 'Coupon created successfully.');
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validatedData($request));

        $this->activityLogService->log($request->user(), 'coupon.updated', $coupon, 'Coupon updated.');

        return back()->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        $this->activityLogService->log($request->user(), 'coupon.deleted', $coupon, 'Coupon deleted.');

        return back()->with('success', 'Coupon deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percentage'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'used_count' => ['nullable', 'integer', 'min:0'],
        ]) + [
            'code' => strtoupper($request->string('code')->value()),
            'is_active' => $request->boolean('is_active', true),
            'used_count' => $request->integer('used_count', 0),
            'min_amount' => $request->input('min_amount', 0),
        ];
    }
}

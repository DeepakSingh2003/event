<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeatType;
use App\Models\ShowSeat;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeatTypeController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(): View
    {
        $seatTypes = SeatType::query()
            ->withCount('showSeats')
            ->orderByDesc('is_active')
            ->orderByDesc('price_multiplier')
            ->paginate(12);

        return view('admin.seat-types.index', compact('seatTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['code'] = Str::upper($data['code'] ?: Str::slug($data['name'], '_'));
        $data['is_active'] = $request->boolean('is_active', true);

        $seatType = SeatType::create($data);

        $this->activityLogService->log($request->user(), 'seat_type.created', $seatType, 'Seat type created.');

        return back()->with('success', 'Seat type created successfully.');
    }

    public function update(Request $request, SeatType $seatType): RedirectResponse
    {
        $data = $this->validatedData($request, $seatType);
        $data['code'] = $this->canChangeCode($seatType)
            ? Str::upper($data['code'] ?: Str::slug($data['name'], '_'))
            : $seatType->code;
        $data['is_active'] = $request->boolean('is_active');

        $seatType->update($data);

        if ($request->boolean('apply_existing')) {
            ShowSeat::query()
                ->where('seat_type_id', $seatType->id)
                ->whereNotIn('status', ['booked', 'locked'])
                ->update([
                    'price' => DB::raw('ROUND(base_price * '.((float) $seatType->price_multiplier).', 2)'),
                    'updated_at' => now(),
                ]);
        }

        $this->activityLogService->log($request->user(), 'seat_type.updated', $seatType, 'Seat type updated.');

        return back()->with('success', 'Seat type updated successfully.');
    }

    public function destroy(Request $request, SeatType $seatType): RedirectResponse
    {
        if ($seatType->showSeats()->exists()) {
            $seatType->update(['is_active' => false]);

            return back()->with('success', 'Seat type is used by existing seats, so it was marked inactive.');
        }

        $seatType->delete();

        $this->activityLogService->log($request->user(), 'seat_type.deleted', $seatType, 'Seat type deleted.');

        return back()->with('success', 'Seat type deleted successfully.');
    }

    private function validatedData(Request $request, ?SeatType $seatType = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('seat_types', 'name')->ignore($seatType)],
            'code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('seat_types', 'code')->ignore($seatType)],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'price_multiplier' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'apply_existing' => ['nullable', 'boolean'],
        ]);
    }

    private function canChangeCode(SeatType $seatType): bool
    {
        if (in_array($seatType->code, ['VIP', 'GOLD', 'SILVER', 'NORMAL'], true)) {
            return false;
        }

        return ! $seatType->showSeats()->exists();
    }
}

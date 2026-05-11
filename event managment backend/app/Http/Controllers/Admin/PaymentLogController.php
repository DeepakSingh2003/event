<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentLogController extends Controller
{
    public function index(Request $request): View
    {
        $paymentLogs = PaymentLog::query()
            ->with(['booking.event', 'user'])
            ->when($request->string('gateway')->value(), fn ($query, $gateway) => $query->where('gateway', $gateway))
            ->when($request->string('status')->value(), fn ($query, $status) => $query->where('status', $status))
            ->latest('logged_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.payment-logs.index', compact('paymentLogs'));
    }
}

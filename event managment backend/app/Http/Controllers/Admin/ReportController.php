<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Event;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function index(Request $request): View
    {
        $report = $this->reportService->metrics($request->all());
        $cities = City::query()->orderBy('name')->get();
        $events = Event::query()->orderBy('title')->get();

        return view('admin.reports.index', compact('report', 'cities', 'events'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $report = $this->reportService->metrics($request->all());

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Revenue', $report['revenue']]);
            fputcsv($handle, ['Bookings', $report['bookings']]);
            fputcsv($handle, ['Cancelled', $report['cancelled']]);
            fputcsv($handle, ['Failed Payments', $report['failed_payments']]);
            fclose($handle);
        }, 'reports.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->reportService->metrics($request->all());

        return Pdf::loadView('pdf.reports', compact('report'))
            ->download('reports.pdf');
    }
}

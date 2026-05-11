<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
    <h1>Revenue Report</h1>
    <ul>
        <li>Total Revenue: {{ \App\Support\Currency::inr($report['revenue']) }}</li>
        <li>Total Bookings: {{ $report['bookings'] }}</li>
        <li>Cancelled Bookings: {{ $report['cancelled'] }}</li>
        <li>Failed Payments: {{ $report['failed_payments'] }}</li>
    </ul>
</body>
</html>

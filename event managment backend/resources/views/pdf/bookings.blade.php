<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bookings Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; font-size: 12px; }
        th { background: #f8fafc; }
    </style>
</head>
<body>
    <h1>Bookings Export</h1>
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>User</th>
                <th>Event</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_reference }}</td>
                    <td>{{ $booking->user?->name }}</td>
                    <td>{{ $booking->event?->title }}</td>
                    <td>{{ $booking->status }}</td>
                    <td>{{ $booking->payment_status }}</td>
                    <td>{{ \App\Support\Currency::inr($booking->total_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

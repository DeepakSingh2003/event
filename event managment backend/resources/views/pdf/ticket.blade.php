<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
        }

        .wrapper {
            width: 900px;
            margin: 40px auto;
        }

        .ticket {
            display: flex;
            border-radius: 20px;
            overflow: hidden;
            color: white;
            background: linear-gradient(135deg, #2d0b59, #6a00f4);
        }

        .left {
            width: 30%;
            background: linear-gradient(180deg, #ff00cc, #7b2ff7);
            padding: 25px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .right {
            width: 70%;
            padding: 30px;
            position: relative;
        }

        .divider {
            width: 2px;
            background: repeating-linear-gradient(
                to bottom,
                white,
                white 6px,
                transparent 6px,
                transparent 12px
            );
        }

        .small {
            font-size: 12px;
            opacity: 0.8;
        }

        .event-title {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }

        .info {
            margin-top: 8px;
            font-size: 14px;
        }

        .seat {
            font-size: 20px;
            font-weight: bold;
        }

        .qr {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 8px;
            border-radius: 10px;
        }

        .label {
            font-size: 11px;
            opacity: 0.7;
        }

    </style>
</head>

<body>

<div class="wrapper">

    <div class="ticket">

        <!-- LEFT -->
        <div class="left">
            <div>
                <div class="label">SEATS</div>
                <div class="seat">
                    {{ $booking->items->pluck('seat_number')->join(', ') }}
                </div>
            </div>

            <div>
                <div class="label">BOOKING ID</div>
                <div class="small">{{ $booking->booking_reference }}</div>
            </div>
        </div>

        <!-- DIVIDER -->
        <div class="divider"></div>

        <!-- RIGHT -->
        <div class="right">

            <div class="small">EVENT</div>

            <div class="event-title">
                {{ $booking->event->title }}
            </div>

            <div class="info">
                {{ $booking->show->show_date->format('d M Y') }} |
                {{ \Carbon\Carbon::parse($booking->show->show_time)->format('h:i A') }}
            </div>

            <div class="info">
                Venue: {{ $booking->show->venue->name }}
            </div>

            <div class="info">
                {{ $booking->user->name }} ({{ $booking->user->email }})
            </div>

            <div class="info">
                Amount: {{ \App\Support\Currency::inr($booking->total_amount) }}
            </div>

            <!-- QR -->
            <div class="qr">
                {!! $qrSvg !!}
            </div>

        </div>

    </div>

</div>

</body>
</html>
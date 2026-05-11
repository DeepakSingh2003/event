<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Support\Currency;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmed - '.$this->booking->booking_reference)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your booking has been confirmed successfully.')
            ->line('Booking Reference: '.$this->booking->booking_reference)
            ->line('Event: '.$this->booking->event->title)
            ->line('Amount: '.Currency::inr($this->booking->total_amount))
            ->action('View Booking', route('admin.bookings.show', $this->booking))
            ->line('Thank you for booking with us.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Booking confirmed',
            'message' => 'Booking '.$this->booking->booking_reference.' has been confirmed.',
            'booking_id' => $this->booking->id,
        ];
    }
}

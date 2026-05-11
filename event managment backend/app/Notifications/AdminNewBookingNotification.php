<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewBookingNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Booking Alert - '.$this->booking->booking_reference)
            ->line('A new booking has been created in the admin system.')
            ->line('Event: '.$this->booking->event->title)
            ->line('User: '.$this->booking->user->name)
            ->line('Seats: '.$this->booking->seats)
            ->action('Open Booking', route('admin.bookings.show', $this->booking));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New booking received',
            'message' => $this->booking->booking_reference.' was created for '.$this->booking->event->title.'.',
            'booking_id' => $this->booking->id,
        ];
    }
}

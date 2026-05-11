<?php

namespace App\Services;

use App\Models\Booking;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    public function qrSvg(Booking $booking): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(180),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($booking->booking_reference.'|'.$booking->qr_token);
    }

    public function generatePdf(Booking $booking): string
    {
        $path = 'tickets/'.$booking->booking_reference.'.pdf';

        $pdf = Pdf::loadView('pdf.ticket', [
            'booking' => $booking->loadMissing(['user', 'event.eventCategory', 'show.venue', 'items']),
            'qrSvg' => $this->qrSvg($booking),
        ]);

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}

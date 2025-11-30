<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Venta;
use Illuminate\Mail\Mailables\Address;

class facturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $venta;

    /**
     * Create a new message instance.
     */
    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recibo: ' . $this->venta->idven,
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail-format.factura',
            with: [
                'venta' => $this->venta,
            ],
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('mail-format.factura')
                    ->with([
                        'venta' => $this->venta,
                    ])
                    ->from(config('mail.from.address'), config('mail.from.name'));
    }
}

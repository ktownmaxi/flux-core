<?php

namespace FluxErp\Mail;

use FluxErp\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __(
                'Order Confirmation for order :order_number',
                [
                    'order_number' => $this->order->order_number,
                ]
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'flux::emails.orders.order-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;

class ContactMessageNotification extends Mailable
{
    public function __construct(public ContactMessage $messageModel)
    {
    }

    public function build(): static
    {
        $subject = $this->messageModel->subject ?: 'New contact message';

        return $this
            ->subject('New contact message: '.$subject)
            ->text('emails.contact-message-notification');
    }
}

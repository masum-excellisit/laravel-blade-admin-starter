<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;

class ContactAutoReply extends Mailable
{
    public function __construct(
        public ContactMessage $messageModel,
        public string $subjectLine,
        public string $body,
    ) {
    }

    public function build(): static
    {
        return $this
            ->subject($this->subjectLine)
            ->text('emails.contact-auto-reply');
    }
}

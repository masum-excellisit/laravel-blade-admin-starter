<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageNotification;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function show()
    {
        return view('site.contact');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = ContactMessage::create($data);

        Activity::log('created', $message, 'Contact message submitted', [
            'email' => $message->email,
            'subject' => $message->subject,
        ]);

        $this->sendNotification($message);
        $this->sendAutoReply($message);

        return back()->with('success', 'Thanks! Your message has been sent.');
    }

    private function sendNotification(ContactMessage $message): void
    {
        $recipient = trim((string) (Setting::get('notify_contact_email') ?: Setting::get('contact_email')));
        if ($recipient === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new ContactMessageNotification($message));
        } catch (Throwable $e) {
            Activity::log('mail_failed', $message, 'Contact notification email failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendAutoReply(ContactMessage $message): void
    {
        if (! $this->settingEnabled(Setting::get('notify_auto_reply', '0'))) {
            return;
        }

        try {
            Mail::to($message->email)->send(new ContactAutoReply(
                $message,
                Setting::get('notify_auto_reply_subject', 'Thanks for contacting us'),
                Setting::get('notify_auto_reply_body', 'Thanks for reaching out. We received your message and will respond soon.'),
            ));
        } catch (Throwable $e) {
            Activity::log('mail_failed', $message, 'Contact auto-reply email failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function settingEnabled(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}

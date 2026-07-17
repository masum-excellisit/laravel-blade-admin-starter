<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Mail\Mailable;

class JobApplicationNotification extends Mailable
{
    public function __construct(public JobApplication $application)
    {
        $this->application->loadMissing('jobListing');
    }

    public function build(): static
    {
        $title = $this->application->jobListing?->title ?? 'job';

        return $this
            ->subject('New job application: '.$title)
            ->text('emails.job-application-notification');
    }
}

<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\JobApplicationNotification;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Setting;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class JobController extends Controller
{
    public function index()
    {
        $jobs = JobListing::published()->latest('published_at')->get();

        return view('site.jobs.index', compact('jobs'));
    }

    public function show(string $slug)
    {
        $job = JobListing::published()->where('slug', $slug)->firstOrFail();

        return view('site.jobs.show', compact('job'));
    }

    public function apply(Request $request, string $slug)
    {
        $job = JobListing::published()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $path = null;
        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
        }

        $application = JobApplication::create([
            'job_listing_id' => $job->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'resume_path' => $path,
            'status' => 'new',
        ]);

        Activity::log('created', $application, 'Job application submitted', [
            'job' => $job->title,
            'email' => $application->email,
        ]);

        $this->sendApplicationNotification($application);

        return back()->with('success', 'Application submitted. Thank you!');
    }

    private function sendApplicationNotification(JobApplication $application): void
    {
        if (! $this->settingEnabled(Setting::get('notify_job_applications', '0'))) {
            return;
        }

        $recipient = trim((string) (Setting::get('notify_contact_email') ?: Setting::get('contact_email')));
        if ($recipient === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new JobApplicationNotification($application));
        } catch (Throwable $e) {
            Activity::log('mail_failed', $application, 'Job application notification email failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function settingEnabled(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}

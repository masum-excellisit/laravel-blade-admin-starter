<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Http\Request;

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

        JobApplication::create([
            'job_listing_id' => $job->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'resume_path' => $path,
            'status' => 'new',
        ]);

        return back()->with('success', 'Application submitted. Thank you!');
    }
}

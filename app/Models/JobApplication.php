<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JobApplication extends Model
{
    protected $fillable = [
        'job_listing_id', 'name', 'email', 'phone', 'resume_path', 'cover_letter', 'status',
    ];

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function resumeUrl(): ?string
    {
        return $this->resume_path ? Storage::disk('public')->url($this->resume_path) : null;
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JobListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('jobListing')?->id ?? $this->route('job')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('job_listings', 'slug')->ignore($id)],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'in:full-time,part-time,contract,remote'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->slug)]);
        } elseif ($this->filled('title')) {
            $this->merge(['slug' => Str::slug($this->title)]);
        }
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('block')?->id;
        $contentRules = $this->input('type') === 'json'
            ? ['nullable', 'json']
            : ['nullable', 'string'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('content_blocks', 'key')->ignore($id)],
            'type' => ['required', 'in:html,richtext,json'],
            'content' => $contentRules,
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'key' => $this->filled('key') ? Str::slug($this->key, '-') : Str::slug((string) $this->name, '-'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

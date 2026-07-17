<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('form')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('forms', 'slug')->ignore($id)],
            'success_message' => ['required', 'string', 'max:255'],
            'notify_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['required_with:fields', 'string', 'max:255'],
            'fields.*.name' => ['required_with:fields', 'string', 'max:255', 'alpha_dash'],
            'fields.*.type' => ['required_with:fields', 'in:text,email,textarea,select,checkbox,number,tel,url'],
            'fields.*.options' => ['nullable', 'string'],
            'fields.*.required' => ['boolean'],
            'fields.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $fields = collect($this->input('fields', []))
            ->map(function (array $field, int $index) {
                $label = $field['label'] ?? '';

                return array_merge($field, [
                    'name' => filled($field['name'] ?? null)
                        ? Str::slug((string) $field['name'], '_')
                        : Str::slug((string) $label, '_'),
                    'required' => ! empty($field['required']),
                    'sort_order' => $field['sort_order'] ?? ($index * 10),
                ]);
            })
            ->filter(fn (array $field) => filled($field['label'] ?? null) || filled($field['name'] ?? null))
            ->values()
            ->all();

        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug($this->slug, '-') : Str::slug((string) $this->name, '-'),
            'is_active' => $this->boolean('is_active'),
            'fields' => $fields,
        ]);
    }
}

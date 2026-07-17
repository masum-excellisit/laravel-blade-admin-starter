<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class FormController extends Controller
{
    public function show(string $slug)
    {
        $form = Form::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        return view('site.forms.show', compact('form'));
    }

    public function submit(Request $request, string $slug)
    {
        $form = Form::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        $data = $request->validate($this->rulesFor($form));
        $payload = $this->payloadFor($form, $data, $request);

        $form->submissions()->create([
            'data' => $payload,
            'ip_address' => $request->ip(),
        ]);

        $this->sendNotification($form, $payload);

        return back()->with('success', $form->success_message);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rulesFor(Form $form): array
    {
        return $form->fields->mapWithKeys(function (FormField $field) {
            $rules = [$field->required ? 'required' : 'nullable'];

            return [$field->name => array_merge($rules, match ($field->type) {
                'email' => ['email', 'max:255'],
                'number' => ['numeric'],
                'url' => ['url', 'max:2048'],
                'tel' => ['string', 'max:50'],
                'textarea' => ['string', 'max:5000'],
                'select' => [Rule::in($field->options ?? [])],
                'checkbox' => [$field->required ? 'accepted' : 'boolean'],
                default => ['string', 'max:255'],
            })];
        })->all();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function payloadFor(Form $form, array $validated, Request $request): array
    {
        return $form->fields->mapWithKeys(function (FormField $field) use ($validated, $request) {
            $value = $field->type === 'checkbox'
                ? $request->boolean($field->name)
                : ($validated[$field->name] ?? null);

            return [$field->name => $value];
        })->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function sendNotification(Form $form, array $payload): void
    {
        if (! $form->notify_email) {
            return;
        }

        try {
            $summary = collect($payload)
                ->map(fn (mixed $value, string $key) => Str::headline($key).': '.$this->displayValue($value))
                ->implode(PHP_EOL);

            Mail::raw("New submission for {$form->name}:".PHP_EOL.PHP_EOL.$summary, function ($message) use ($form) {
                $message->to($form->notify_email)
                    ->subject("New form submission: {$form->name}");
            });
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    protected function displayValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'Yes' : 'No',
            is_array($value) => implode(', ', $value),
            $value === null || $value === '' => '—',
            default => (string) $value,
        };
    }
}

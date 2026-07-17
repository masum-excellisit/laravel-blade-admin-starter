<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FormRequest;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FormController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('forms.view'), 403);

        $forms = $this->applyListQuery(
            Form::query()->withCount('submissions'),
            $request,
            searchable: ['name', 'slug', 'notify_email'],
            sortable: ['name', 'slug', 'is_active', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc',
        )
            ->when($request->filled('active'), fn (Builder $query) => $query->where('is_active', $request->boolean('active')))
            ->paginate(12)
            ->withQueryString();

        return view('admin.forms.index', compact('forms'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('forms.create'), 403);

        return view('admin.forms.create', [
            'form' => new Form([
                'success_message' => 'Thanks — we received your submission.',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(FormRequest $request)
    {
        abort_unless($request->user()->can('forms.create'), 403);

        $form = Form::create($request->safe()->except('fields'));
        $this->syncFields($form, $request->validated('fields', []));

        return redirect()->route('admin.forms.index')->with('success', 'Form created.');
    }

    public function edit(Form $form)
    {
        abort_unless(auth()->user()->can('forms.view'), 403);

        $form->load('fields');

        return view('admin.forms.edit', compact('form'));
    }

    public function update(FormRequest $request, Form $form)
    {
        abort_unless($request->user()->can('forms.edit'), 403);

        $form->update($request->safe()->except('fields'));
        $this->syncFields($form, $request->validated('fields', []));

        return redirect()->route('admin.forms.index')->with('success', 'Form updated.');
    }

    public function destroy(Request $request, Form $form)
    {
        abort_unless($request->user()->can('forms.delete'), 403);

        $form->delete();

        return back()->with('success', 'Form deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction(
            $request,
            Form::class,
            'forms',
            function (Builder $query, string $action) use ($request) {
                match ($action) {
                    'delete' => tap($query)->get()->each(function (Form $form) use ($request) {
                        abort_unless($request->user()->can('forms.delete'), 403);
                        $form->delete();
                    }),
                    'activate' => $this->bulkSetStatus($request, $query, 'forms', true, 'is_active'),
                    'deactivate' => $this->bulkSetStatus($request, $query, 'forms', false, 'is_active'),
                    default => abort(422, 'Unknown bulk action.'),
                };
            },
        );
    }

    public function submissions(Form $form)
    {
        abort_unless(auth()->user()->can('forms.view'), 403);

        $submissions = $form->submissions()
            ->latest()
            ->paginate(20);

        return view('admin.forms.submissions.index', compact('form', 'submissions'));
    }

    public function showSubmission(Form $form, FormSubmission $submission)
    {
        abort_unless(auth()->user()->can('forms.view'), 403);
        abort_unless($submission->form_id === $form->id, 404);

        return view('admin.forms.submissions.show', compact('form', 'submission'));
    }

    public function destroySubmission(Request $request, Form $form, FormSubmission $submission)
    {
        abort_unless($request->user()->can('forms.delete'), 403);
        abort_unless($submission->form_id === $form->id, 404);

        $submission->delete();

        return redirect()->route('admin.forms.submissions.index', $form)->with('success', 'Submission deleted.');
    }

    /**
     * Replace the builder field list with the submitted repeater rows.
     *
     * @param  array<int, array<string, mixed>>  $fields
     */
    protected function syncFields(Form $form, array $fields): void
    {
        $form->fields()->delete();

        foreach ($fields as $index => $field) {
            $form->fields()->create([
                'label' => $field['label'],
                'name' => $field['name'],
                'type' => $field['type'],
                'options' => $this->parseOptions($field['options'] ?? null),
                'required' => (bool) ($field['required'] ?? false),
                'sort_order' => $field['sort_order'] ?? ($index * 10),
            ]);
        }
    }

    protected function parseOptions(?string $options): ?array
    {
        $values = collect(explode(',', (string) $options))
            ->map(fn (string $option) => trim($option))
            ->filter()
            ->values()
            ->all();

        return $values === [] ? null : $values;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsContent;
use App\Models\Revision;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('cms.view'), 403);

        $pages = collect(config('cms.pages', []))
            ->map(fn (array $config, string $key) => [
                'key' => $key,
                'title' => $config['title'],
            ])
            ->values();

        return view('admin.cms.index', compact('pages'));
    }

    public function edit(Request $request, string $page)
    {
        abort_unless($request->user()->can('cms.view'), 403);

        $pageConfig = config("cms.pages.{$page}");
        abort_if(! $pageConfig, 404);

        $sections = $pageConfig['sections'];
        $content = [];

        foreach (array_keys($sections) as $sectionKey) {
            $content[$sectionKey] = CmsContent::getSection($page, $sectionKey);
        }

        return view('admin.cms.edit', [
            'page' => $page,
            'pageTitle' => $pageConfig['title'],
            'sections' => $sections,
            'content' => $content,
        ]);
    }

    public function update(Request $request, string $page)
    {
        abort_unless($request->user()->can('cms.edit'), 403);

        $pageConfig = config("cms.pages.{$page}");
        abort_if(! $pageConfig, 404);

        $rules = $this->buildValidationRules($pageConfig['sections']);
        $validated = $request->validate($rules);
        $changed = false;

        foreach ($pageConfig['sections'] as $sectionKey => $sectionConfig) {
            $sectionInput = $validated['sections'][$sectionKey] ?? [];
            $existing = CmsContent::query()
                ->where('page', $page)
                ->where('section', $sectionKey)
                ->first();
            $existingData = $existing?->data ?? [];
            $sectionData = [];

            foreach ($sectionConfig['fields'] as $field) {
                $fieldKey = $field['key'];
                $fileKey = "sections.{$sectionKey}.{$fieldKey}";
                $existingKey = "sections.{$sectionKey}.{$fieldKey}_existing";

                if ($field['type'] === 'image') {
                    if ($request->hasFile($fileKey)) {
                        $oldPath = $existingData[$fieldKey] ?? null;
                        if ($oldPath) {
                            Storage::disk('public')->delete($oldPath);
                        }
                        $sectionData[$fieldKey] = $request->file($fileKey)->store('cms', 'public');
                    } elseif ($request->filled($existingKey)) {
                        $sectionData[$fieldKey] = $request->input($existingKey);
                    } else {
                        $sectionData[$fieldKey] = $existingData[$fieldKey] ?? null;
                    }
                } else {
                    $sectionData[$fieldKey] = $sectionInput[$fieldKey] ?? null;
                }
            }

            if ($existing && $existing->data !== $sectionData) {
                $existing->recordRevision('before update');
                $changed = true;
            } elseif (! $existing) {
                $changed = true;
            }

            CmsContent::updateOrCreate(
                ['page' => $page, 'section' => $sectionKey],
                ['data' => $sectionData],
            );
        }

        CmsContent::forgetPageCache($page);

        if ($changed) {
            Activity::log('updated', null, 'CMS page content updated', ['page' => $page]);
        }

        return redirect()
            ->route('admin.cms.edit', $page)
            ->with('success', 'Page content updated.');
    }

    public function restoreRevision(Request $request, string $page, Revision $revision)
    {
        abort_unless($request->user()->can('cms.edit'), 403);

        $content = $revision->revisionable;
        abort_unless($content instanceof CmsContent && $content->page === $page, 404);

        $content->restoreRevision($revision);
        CmsContent::forgetPageCache($page);
        Activity::log('restored', $content, 'CMS content revision restored', [
            'page' => $page,
            'section' => $content->section,
            'revision_id' => $revision->id,
        ]);

        return redirect()->route('admin.cms.edit', $page)->with('success', 'CMS revision restored.');
    }

    /**
     * @param  array<string, array{label: string, fields: array<int, array{key: string, type: string, label: string}>}>  $sections
     * @return array<string, mixed>
     */
    protected function buildValidationRules(array $sections): array
    {
        $rules = ['sections' => ['required', 'array']];

        foreach ($sections as $sectionKey => $sectionConfig) {
            $rules["sections.{$sectionKey}"] = ['array'];

            foreach ($sectionConfig['fields'] as $field) {
                $fieldKey = $field['key'];
                $fieldRules = match ($field['type']) {
                    'text' => ['nullable', 'string', 'max:255'],
                    'textarea', 'richtext' => ['nullable', 'string'],
                    'url' => ['nullable', 'string', 'max:2048', 'url'],
                    'image' => ['nullable', 'image', 'max:4096'],
                    default => ['nullable', 'string'],
                };

                $rules["sections.{$sectionKey}.{$fieldKey}"] = $fieldRules;
                if ($field['type'] === 'image') {
                    $rules["sections.{$sectionKey}.{$fieldKey}_existing"] = ['nullable', 'string', 'max:500'];
                }
            }
        }

        return $rules;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\CmsContent;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CmsController extends Controller
{
    public function edit(string $page)
    {
        abort_unless(auth()->user()->can('cms.view'), 403);

        $schema = config("cms.pages.{$page}");
        abort_unless($schema, 404);

        $contents = CmsContent::query()
            ->where('page', $page)
            ->get()
            ->keyBy('section');

        $revisions = Revision::query()
            ->where('revisionable_type', CmsContent::class)
            ->whereIn('revisionable_id', $contents->pluck('id'))
            ->with('user:id,name')
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.cms.edit', compact('page', 'schema', 'contents', 'revisions'));
    }

    public function update(Request $request, string $page)
    {
        abort_unless($request->user()->can('cms.edit'), 403);

        $schema = config("cms.pages.{$page}");
        abort_unless($schema, 404);

        $payload = $request->input('sections', []);
        if (! is_array($payload)) {
            throw ValidationException::withMessages(['sections' => 'Invalid CMS payload.']);
        }

        $changed = false;

        foreach ($schema['sections'] as $sectionKey => $section) {
            $sectionData = $payload[$sectionKey] ?? [];
            if (! is_array($sectionData)) {
                $sectionData = [];
            }

            $existing = CmsContent::query()
                ->where('page', $page)
                ->where('section', $sectionKey)
                ->first();

            $current = is_array($existing?->data) ? $existing->data : [];

            foreach ($section['fields'] as $fieldKey => $field) {
                if (($field['type'] ?? 'text') !== 'image') {
                    continue;
                }

                $upload = $request->file("sections.{$sectionKey}.{$fieldKey}");
                if ($upload) {
                    $path = $upload->store('cms', 'public');
                    $old = $current[$fieldKey] ?? null;
                    if (is_string($old) && $old !== '' && $old !== $path) {
                        Storage::disk('public')->delete($old);
                    }
                    $sectionData[$fieldKey] = $path;
                } else {
                    $sectionData[$fieldKey] = $current[$fieldKey] ?? ($sectionData[$fieldKey] ?? null);
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

        return back()->with('success', 'Revision restored.');
    }

    public function preview(string $page)
    {
        abort_unless(auth()->user()->can('cms.view'), 403);
        abort_unless(config("cms.pages.{$page}"), 404);

        $map = [
            'home' => '/',
            'about' => '/about',
            'services' => '/services',
            'contact' => '/contact',
            'careers' => '/careers',
        ];

        $path = $map[$page] ?? '/';

        return redirect()->to($path.'?cms_preview=1');
    }
}

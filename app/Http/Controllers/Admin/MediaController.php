<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Models\CmsContent;
use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class MediaController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $query = Media::query();
        if ($request->filled('folder')) {
            $query->where('folder', $request->input('folder'));
        }

        $media = $this->applyListQuery(
            $query,
            $request,
            ['name', 'folder', 'alt_text', 'tags'],
            ['created_at', 'name', 'folder'],
        )->paginate(24)->withQueryString();

        $folders = Media::query()
            ->whereNotNull('folder')
            ->where('folder', '!=', '')
            ->distinct()
            ->orderBy('folder')
            ->pluck('folder');

        return view('admin.media.index', compact('media', 'folders'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Media::class, 'media', function ($query, $action, $ids) use ($request) {
            match ($action) {
                'delete' => tap($query)->get()->each(function (Media $medium) use ($request) {
                    abort_unless($request->user()->can('media.delete'), 403);
                    Storage::disk($medium->disk)->delete($medium->path);
                    $medium->delete();
                }),
                default => abort(422, 'Unknown bulk action.'),
            };
        });
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('media.create'), 403);
        $data = $request->validate([
            'files.*' => ['required', 'file', 'max:8192'],
            'folder' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($request->file('files', []) as $file) {
            $this->save($file, $request->user()->id, $data);
        }

        return back()->with('success', 'Files uploaded.');
    }

    public function edit(Media $medium)
    {
        abort_unless(request()->user()->can('media.edit'), 403);

        return view('admin.media.edit', ['medium' => $medium]);
    }

    public function update(Request $request, Media $medium)
    {
        abort_unless($request->user()->can('media.edit'), 403);
        $data = $request->validate([
            'folder' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'replacement' => ['nullable', 'file', 'max:8192'],
        ]);

        $oldDisk = $medium->disk;
        $oldPath = $medium->path;

        $medium->fill([
            'folder' => $data['folder'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'tags' => $data['tags'] ?? null,
        ]);

        if ($request->hasFile('replacement')) {
            $replacement = $this->storedFileAttributes($request->file('replacement'));
            $medium->fill($replacement);
        }

        $medium->save();

        if ($request->hasFile('replacement')) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        return back()->with('success', 'Media updated.');
    }

    public function cleanup(Request $request)
    {
        abort_unless($request->user()->can('media.delete'), 403);

        $deleted = 0;
        foreach ($this->unusedMedia() as $medium) {
            Storage::disk($medium->disk)->delete($medium->path);
            $medium->delete();
            $deleted++;
        }

        return back()->with('success', "{$deleted} unused media file(s) deleted.");
    }

    public function destroy(Request $request, Media $medium)
    {
        abort_unless($request->user()->can('media.delete'), 403);
        Storage::disk($medium->disk)->delete($medium->path);
        $medium->delete();

        return back()->with('success', 'File deleted.');
    }

    // Jodit editor upload endpoint
    public function jodit(Request $request)
    {
        abort_unless($request->user()?->can('media.create'), 403);
        $request->validate(['files.*' => ['required', 'image', 'max:8192']]);

        $urls = [];
        foreach ($request->file('files', []) as $file) {
            $media = $this->save($file, $request->user()->id);
            $urls[] = Storage::disk('public')->url($media->path);
        }

        return response()->json(['success' => true, 'files' => $urls]);
    }

    protected function save($file, ?int $userId, array $metadata = []): Media
    {
        return Media::create(array_merge(
            $this->storedFileAttributes($file),
            [
                'folder' => $metadata['folder'] ?? null,
                'alt_text' => $metadata['alt_text'] ?? null,
                'tags' => $metadata['tags'] ?? null,
                'user_id' => $userId,
            ],
        ));
    }

    protected function storedFileAttributes($file): array
    {
        $name = $file->getClientOriginalName();
        $path = $file->store('media', 'public');

        // Resize large images in place to keep uploads lean.
        if (str_starts_with((string) $file->getMimeType(), 'image/')) {
            $full = Storage::disk('public')->path($path);
            $img = Image::read($full);
            if ($img->width() > 1600) {
                $img->scaleDown(width: 1600)->save($full);
            }
        }

        return [
            'name' => $name,
            'path' => $path,
            'disk' => 'public',
            'mime' => $file->getClientMimeType(),
            'size' => Storage::disk('public')->size($path),
        ];
    }

    protected function unusedMedia()
    {
        return Media::query()->get()->reject(fn (Media $medium) => $this->isReferenced($medium));
    }

    protected function isReferenced(Media $medium): bool
    {
        $needles = array_values(array_unique(array_filter([
            $medium->path,
            Storage::disk($medium->disk)->url($medium->path),
        ])));

        return $this->tableContains(Page::class, ['body'], $needles)
            || $this->tableContains(Post::class, ['body', 'featured_image', 'og_image'], $needles)
            || $this->tableContains(Service::class, ['image'], $needles)
            || $this->tableContains(Setting::class, ['value'], $needles)
            || $this->tableContains(TeamMember::class, ['photo'], $needles)
            || $this->tableContains(PortfolioItem::class, ['image'], $needles)
            || $this->tableContains(CmsContent::class, ['data'], $needles);
    }

    protected function tableContains(string $model, array $columns, array $needles): bool
    {
        return $model::query()->where(function ($query) use ($columns, $needles) {
            foreach ($columns as $column) {
                foreach ($needles as $needle) {
                    $query->orWhere($column, 'like', '%'.addcslashes($needle, '\%_').'%');
                }
            }
        })->exists();
    }
}

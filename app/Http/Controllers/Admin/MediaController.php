<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class MediaController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $media = $this->applyListQuery(
            Media::query(),
            $request,
            ['name'],
            ['created_at'],
        )->paginate(24)->withQueryString();

        return view('admin.media.index', compact('media'));
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
        $request->validate(['files.*' => ['required', 'file', 'max:8192']]);

        foreach ($request->file('files', []) as $file) {
            $this->save($file, $request->user()->id);
        }

        return back()->with('success', 'Files uploaded.');
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

    protected function save($file, ?int $userId): Media
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

        return Media::create([
            'name' => $name,
            'path' => $path,
            'disk' => 'public',
            'mime' => $file->getClientMimeType(),
            'size' => Storage::disk('public')->size($path),
            'user_id' => $userId,
        ]);
    }
}

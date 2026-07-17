<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsContent;
use App\Models\ContentBlock;
use App\Models\Faq;
use App\Models\Form;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{
    private const MEDIA_ZIP_SIZE_LIMIT = 52428800;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('backups.view'), 403);

        return view('admin.backups.index');
    }

    public function download(Request $request)
    {
        abort_unless($request->user()->can('backups.create'), 403);

        $filename = 'cms-export-'.now()->format('Ymd-His');
        $json = json_encode($this->payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (! class_exists(ZipArchive::class)) {
            return response()->streamDownload(
                fn () => print $json,
                "{$filename}.json",
                ['Content-Type' => 'application/json']
            );
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $path = "{$tempDir}/{$filename}.zip";
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('content.json', $json);
        $this->addMediaFolder($zip);
        $zip->close();

        return response()->download($path, "{$filename}.zip")->deleteFileAfterSend(true);
    }

    protected function payload(): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'pages' => Page::query()->orderBy('id')->get()->toArray(),
            'posts' => Post::query()->orderBy('id')->get()->toArray(),
            'menus' => Menu::query()->with('items')->orderBy('id')->get()->toArray(),
            'settings' => Setting::query()
                ->where('key', 'not like', '%password%')
                ->orderBy('group')
                ->orderBy('key')
                ->get()
                ->toArray(),
            'faqs' => Faq::query()->orderBy('id')->get()->toArray(),
            'team' => TeamMember::query()->orderBy('id')->get()->toArray(),
            'portfolio' => PortfolioItem::query()->orderBy('id')->get()->toArray(),
            'blocks' => ContentBlock::query()->orderBy('id')->get()->toArray(),
            'forms' => Form::query()->with('fields')->orderBy('id')->get()->toArray(),
            'redirects' => Redirect::query()->orderBy('id')->get()->toArray(),
            'cms_contents' => CmsContent::query()->orderBy('id')->get()->toArray(),
        ];
    }

    protected function addMediaFolder(ZipArchive $zip): void
    {
        $disk = Storage::disk('public');
        $files = collect($disk->allFiles('media'));
        $totalSize = $files->sum(fn (string $file) => $disk->size($file));

        if ($totalSize > self::MEDIA_ZIP_SIZE_LIMIT) {
            $zip->addFromString('media-skipped.txt', 'The public media folder exceeded the 50 MB export limit.');

            return;
        }

        foreach ($files as $file) {
            $zip->addFile($disk->path($file), $file);
        }
    }
}

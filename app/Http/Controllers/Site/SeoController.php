<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use XMLWriter;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $entries = collect($this->staticUrls())
            ->merge(Page::published()->get()->map(fn (Page $page) => [
                'loc' => url('/'.$page->slug),
                'lastmod' => $this->lastModified($page),
            ]))
            ->merge(Post::published()->get()->map(fn (Post $post) => [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $this->lastModified($post),
            ]))
            ->merge(Service::published()->get()->map(fn (Service $service) => [
                'loc' => route('services.show', $service->slug),
                'lastmod' => $this->lastModified($service),
            ]))
            ->merge(JobListing::published()->get()->map(fn (JobListing $job) => [
                'loc' => route('jobs.show', $job->slug),
                'lastmod' => $this->lastModified($job),
            ]))
            ->merge(PortfolioItem::published()->get()->map(fn (PortfolioItem $item) => [
                'loc' => url('/portfolio/'.$item->slug),
                'lastmod' => $this->lastModified($item),
            ]));

        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($entries as $entry) {
            $xml->startElement('url');
            $xml->writeElement('loc', $entry['loc']);
            if ($entry['lastmod']) {
                $xml->writeElement('lastmod', $entry['lastmod']);
            }
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        return response($xml->outputMemory(), 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ]);

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * @return array<int, array{loc: string, lastmod: null}>
     */
    private function staticUrls(): array
    {
        return [
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('about'), 'lastmod' => null],
            ['loc' => route('how-it-works'), 'lastmod' => null],
            ['loc' => route('careers'), 'lastmod' => null],
            ['loc' => route('services.index'), 'lastmod' => null],
            ['loc' => route('jobs.index'), 'lastmod' => null],
            ['loc' => route('blog.index'), 'lastmod' => null],
            ['loc' => route('contact'), 'lastmod' => null],
        ];
    }

    private function lastModified(Model $model): ?string
    {
        return $model->updated_at?->toDateString();
    }
}

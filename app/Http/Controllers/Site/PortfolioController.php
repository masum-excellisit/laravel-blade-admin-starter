<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;

class PortfolioController extends Controller
{
    public function index()
    {
        $portfolioItems = PortfolioItem::published()
            ->orderBy('sort_order')
            ->latest('published_at')
            ->get();

        return view('site.portfolio.index', compact('portfolioItems'));
    }

    public function show(string $slug)
    {
        $portfolioItem = PortfolioItem::published()->where('slug', $slug)->firstOrFail();

        return view('site.portfolio.show', compact('portfolioItem'));
    }
}

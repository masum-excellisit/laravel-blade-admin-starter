<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::published()->orderBy('sort_order')->orderBy('title')->get();

        return view('site.services.index', compact('services'));
    }

    public function show(string $slug)
    {
        $service = Service::published()->where('slug', $slug)->firstOrFail();

        return view('site.services.show', compact('service'));
    }
}

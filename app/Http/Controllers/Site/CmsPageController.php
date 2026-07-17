<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;

class CmsPageController extends Controller
{
    public function about()
    {
        return view('site.cms.about');
    }

    public function howItWorks()
    {
        return view('site.cms.how-it-works');
    }

    public function careers()
    {
        $testimonials = Testimonial::published()->orderBy('sort_order')->take(3)->get();

        return view('site.cms.careers', [
            'jobs' => \App\Models\JobListing::published()->latest('published_at')->get(),
            'testimonials' => $testimonials,
        ]);
    }
}

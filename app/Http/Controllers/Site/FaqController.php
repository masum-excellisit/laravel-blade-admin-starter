<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::query()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->orderBy('question')
            ->get();

        return view('site.faqs.index', compact('faqs'));
    }
}

<?php

use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\CmsPageController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\FormController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\JobController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [CmsPageController::class, 'about'])->name('about');
Route::get('/how-it-works', [CmsPageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/careers', [CmsPageController::class, 'careers'])->name('careers');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{slug}', [JobController::class, 'show'])->name('jobs.show');
Route::post('/jobs/{slug}/apply', [JobController::class, 'apply'])->name('jobs.apply');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/forms/{slug}', [FormController::class, 'show'])->name('forms.show');
Route::post('/forms/{slug}', [FormController::class, 'submit'])->name('forms.submit');

// Dynamic DB pages — keep last so it doesn't shadow named routes.
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '^(?!admin$|admin/).*')->name('page.show');

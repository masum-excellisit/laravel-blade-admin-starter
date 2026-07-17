<?php

use App\Http\Controllers\Site\AccountController;
use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\CmsPageController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\FaqController;
use App\Http\Controllers\Site\FormController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\JobController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PortfolioController;
use App\Http\Controllers\Site\SeoController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Controllers\Site\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [CmsPageController::class, 'about'])->name('about');
Route::get('/how-it-works', [CmsPageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/careers', [CmsPageController::class, 'careers'])->name('careers');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');
Route::get('/team', [TeamController::class, 'index'])->name('team.index');
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

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

Route::middleware('guest')->group(function () {
    Route::get('/account/login', [AccountController::class, 'showLogin'])->name('account.login');
    Route::post('/account/login', [AccountController::class, 'login'])->name('account.login.submit');
    Route::get('/account/register', [AccountController::class, 'showRegister'])->name('account.register');
    Route::post('/account/register', [AccountController::class, 'register'])->name('account.register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/account/logout', [AccountController::class, 'logout'])->name('account.logout');
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
});

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Dynamic DB pages — keep last so it doesn't shadow named routes.
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '^(?!admin$|admin/).*')->name('page.show');

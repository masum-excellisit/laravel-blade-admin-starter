<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\ContentBlockController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\JobListingController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Guest (auth) routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'attempt'])->name('login.attempt');
    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

// Authenticated admin routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (any authenticated user)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::middleware('permission:users.view')->group(function () {
        Route::post('users/bulk', [UserController::class, 'bulk'])->name('users.bulk');
        Route::resource('users', UserController::class)->except('show');
    });
    Route::middleware('permission:customers.view')->group(function () {
        Route::post('customers/bulk', [CustomerController::class, 'bulk'])->name('customers.bulk');
        Route::resource('customers', CustomerController::class)->except('show');
    });
    Route::middleware('permission:roles.view')->group(function () {
        Route::post('roles/bulk', [RoleController::class, 'bulk'])->name('roles.bulk');
        Route::resource('roles', RoleController::class)->except('show');
    });
    Route::middleware('permission:permissions.view')->group(function () {
        Route::post('permissions/bulk', [PermissionController::class, 'bulk'])->name('permissions.bulk');
        Route::resource('permissions', PermissionController::class)->except('show');
    });
    Route::middleware('permission:cms.view')->group(function () {
        Route::get('cms', [CmsController::class, 'index'])->name('cms.index');
        Route::get('cms/{page}', [CmsController::class, 'edit'])->name('cms.edit');
        Route::put('cms/{page}', [CmsController::class, 'update'])->name('cms.update');
    });

    Route::middleware('permission:services.view')->group(function () {
        Route::post('services/bulk', [ServiceController::class, 'bulk'])->name('services.bulk');
        Route::resource('services', ServiceController::class)->except('show');
    });

    Route::middleware('permission:testimonials.view')->group(function () {
        Route::post('testimonials/bulk', [TestimonialController::class, 'bulk'])->name('testimonials.bulk');
        Route::resource('testimonials', TestimonialController::class)->except('show');
    });

    Route::middleware('permission:jobs.view')->group(function () {
        Route::post('jobs/bulk', [JobListingController::class, 'bulk'])->name('jobs.bulk');
        Route::resource('jobs', JobListingController::class)->except('show')
            ->parameters(['jobs' => 'jobListing']);
    });

    Route::middleware('permission:job-applications.view')->group(function () {
        Route::post('job-applications/bulk', [JobApplicationController::class, 'bulk'])->name('job-applications.bulk');
        Route::get('job-applications', [JobApplicationController::class, 'index'])->name('job-applications.index');
        Route::get('job-applications/{jobApplication}', [JobApplicationController::class, 'show'])->name('job-applications.show');
        Route::put('job-applications/{jobApplication}', [JobApplicationController::class, 'update'])->name('job-applications.update');
        Route::delete('job-applications/{jobApplication}', [JobApplicationController::class, 'destroy'])->name('job-applications.destroy');
    });

    Route::middleware('permission:pages.view')->group(function () {
        Route::post('pages/bulk', [PageController::class, 'bulk'])->name('pages.bulk');
        Route::resource('pages', PageController::class)->except('show');
    });
    Route::middleware('permission:posts.view')->group(function () {
        Route::post('posts/bulk', [PostController::class, 'bulk'])->name('posts.bulk');
        Route::resource('posts', PostController::class)->except('show');
    });
    Route::middleware('permission:categories.view')->group(function () {
        Route::post('categories/bulk', [CategoryController::class, 'bulk'])->name('categories.bulk');
        Route::resource('categories', CategoryController::class)->except('show', 'create', 'edit');
    });
    Route::middleware('permission:menus.view')->group(function () {
        Route::post('menus/bulk', [MenuController::class, 'bulk'])->name('menus.bulk');
        Route::resource('menus', MenuController::class)->except('show');
        Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
        Route::delete('menu-items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');
        Route::post('menus/{menu}/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
    });
    Route::middleware('permission:media.view')->group(function () {
        Route::post('media/bulk', [MediaController::class, 'bulk'])->name('media.bulk');
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');
    });
    Route::post('media/jodit', [MediaController::class, 'jodit'])->name('media.jodit');

    Route::middleware('permission:messages.view')->group(function () {
        Route::post('messages/bulk', [MessageController::class, 'bulk'])->name('messages.bulk');
        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    });

    Route::middleware('permission:blocks.view')->group(function () {
        Route::post('blocks/bulk', [ContentBlockController::class, 'bulk'])->name('blocks.bulk');
        Route::resource('blocks', ContentBlockController::class)->except('show');
    });

    Route::middleware('permission:forms.view')->group(function () {
        Route::post('forms/bulk', [FormController::class, 'bulk'])->name('forms.bulk');
        Route::get('forms/{form}/submissions', [FormController::class, 'submissions'])->name('forms.submissions.index');
        Route::get('forms/{form}/submissions/{submission}', [FormController::class, 'showSubmission'])->name('forms.submissions.show');
        Route::delete('forms/{form}/submissions/{submission}', [FormController::class, 'destroySubmission'])->name('forms.submissions.destroy');
        Route::resource('forms', FormController::class)->except('show');
    });

    // [admin-module routes] — do not remove; make:admin-module injects here (before settings).

    Route::middleware('permission:settings.view')->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/test-mail', [SettingController::class, 'testMail'])->name('settings.test-mail');
    });
});

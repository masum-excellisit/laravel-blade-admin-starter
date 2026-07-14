<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
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
        Route::resource('users', UserController::class)->except('show');
    });
    Route::middleware('permission:customers.view')->group(function () {
        Route::resource('customers', CustomerController::class)->except('show');
    });
    Route::middleware('permission:roles.view')->group(function () {
        Route::resource('roles', RoleController::class)->except('show');
    });
    Route::middleware('permission:permissions.view')->group(function () {
        Route::resource('permissions', PermissionController::class)->except('show');
    });
    Route::middleware('permission:pages.view')->group(function () {
        Route::resource('pages', PageController::class)->except('show');
    });
    Route::middleware('permission:posts.view')->group(function () {
        Route::resource('posts', PostController::class)->except('show');
    });
    Route::middleware('permission:categories.view')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show', 'create', 'edit');
    });
    Route::middleware('permission:menus.view')->group(function () {
        Route::resource('menus', MenuController::class)->except('show');
        Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
        Route::delete('menu-items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');
        Route::post('menus/{menu}/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
    });
    Route::middleware('permission:media.view')->group(function () {
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');
    });
    Route::post('media/jodit', [MediaController::class, 'jodit'])->name('media.jodit');

    Route::middleware('permission:messages.view')->group(function () {
        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    });

    Route::middleware('permission:settings.view')->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/test-mail', [SettingController::class, 'testMail'])->name('settings.test-mail');
    });

    // [admin-module routes] — do not remove; make:admin-module injects here.
});

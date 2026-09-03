<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CmsContent;
use App\Models\ContentBlock;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Observers\AuditableObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');

        // Point password reset links at the admin reset screen.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return route('admin.password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);
        });

        $observer = AuditableObserver::class;
        foreach ([
            User::class,
            Post::class,
            Page::class,
            Category::class,
            Media::class,
            Menu::class,
            MenuItem::class,
            Faq::class,
            PortfolioItem::class,
            Service::class,
            Testimonial::class,
            TeamMember::class,
            JobListing::class,
            JobApplication::class,
            Form::class,
            FormSubmission::class,
            ContentBlock::class,
            CmsContent::class,
            Redirect::class,
            ContactMessage::class,
            Role::class,
            Permission::class,
        ] as $model) {
            $model::observe($observer);
        }
    }
}

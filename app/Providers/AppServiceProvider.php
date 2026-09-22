<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (config('app.url') && str_contains(config('app.url'), 'http')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            if (str_contains(config('app.url'), 'https')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }


        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $pages = \App\Models\Page::where('status', 'aktif')->get();
            $navbarPages = $pages->where('show_in_navbar', true);
            $footerPages = $pages->whereNotNull('footer_column');
            
            $view->with('navbarPages', $navbarPages)
                 ->with('footerPages', $footerPages);
        });
    }
}

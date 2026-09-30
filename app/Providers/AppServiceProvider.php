<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Trip;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'id'));

        $this->configureRateLimiting();
        $this->configureViews();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $key = Str::transliterate(Str::lower($request->input('email', ''))).'|'.$request->ip();

            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->withErrors(['email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam satu menit.'])->withInput();
            });
        });
    }

    private function configureViews(): void
    {
        Paginator::defaultView('pagination.nitip');
        Paginator::defaultSimpleView('pagination.nitip-simple');

        // Navigation counters, computed once per layout render (not per component).
        View::composer(['components.layouts.app', 'components.layouts.admin'], function (\Illuminate\View\View $view) {
            $user = auth()->user();
            $student = $user && $user->isStudent();

            $view->with('nav', [
                'unread' => $user ? $user->unreadNotifications()->count() : 0,
                'open_feed' => $student
                    ? Order::query()->openRequests()->forCampus($user->campus)->count() + Trip::query()->open()->forCampus($user->campus)->count()
                    : 0,
                'active_orders' => $student ? Order::query()->involving($user)->active()->count() : 0,
            ]);
        });
    }
}

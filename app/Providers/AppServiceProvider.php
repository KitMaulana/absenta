<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');
        CarbonImmutable::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian');

        // Identitas kelas dipakai di header publik, sidebar admin, dan kop PDF.
        View::composer('*', function ($view) {
            $view->with('pengaturan', Setting::map());
        });
    }
}

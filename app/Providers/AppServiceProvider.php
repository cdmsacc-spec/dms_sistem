<?php

namespace App\Providers;

use App\Models\Document;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Carbon\Carbon;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        FilamentColor::register([
            'danger' => Color::hex('#FF0000'),   // red-500
            'gray'   => Color::hex('#6B7280'),   // gray-500
            'info'   => Color::hex('#3bc4ff'),   // blue-500
            'success' => Color::hex('#49ED1C'),   // green-500
            'warning' => Color::hex('#e0c828'),   // amber-500
            'white'  => Color::hex('#FFFFFF'),   // white
        ]);
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        date_default_timezone_set(Config::get('app.timezone', 'UTC'));
        FilamentShield::configurePermissionIdentifierUsing(
            fn($resource) => str($resource::getModel())
                ->afterLast('\\')
                ->lower()
                ->toString()
        );
    }
}

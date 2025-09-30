<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Traits\HasRoles;

class StatsOverviewUserResource extends BaseWidget
{
    protected ?string $heading = 'User Information';
    protected int | string | array $columnSpan = 3;

    protected function getStats(): array
    {
        $staff_doc = User::whereHas('roles', function ($q) {
            $q->where('name', 'staff_document');
        })->count();

        $staff_crew = User::whereHas('roles', function ($q) {
            $q->where('name', 'staff_crew');
        })->count();

        $all = User::count();
        return [
            Stat::make('A', $all)
                ->icon('heroicon-o-folder-open')
                ->label(new HtmlString('<span class="text-xs font-medium text-info-600">Total User</span>'))
                ->chart([10, 10])
                ->color('success'),
            Stat::make('A', $staff_crew)
                ->icon('heroicon-o-folder-open')
                ->label(new HtmlString('<span class="text-xs font-medium text-info-600">Total Staff Crew</span>'))
                ->chart([10, 10])
                ->color('info'),
            Stat::make('A', $staff_doc)
                ->icon('heroicon-o-folder-open')
                ->label(new HtmlString('<span class="text-xs font-medium text-info-600">Total Staff Document</span>'))
                ->chart([10, 10])
                ->color('danger'),
        ];
    }
}

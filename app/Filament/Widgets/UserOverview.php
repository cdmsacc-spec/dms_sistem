<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class UserOverview extends BaseWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $roleCounts = DB::table('roles')
            ->select('roles.name', DB::raw('COUNT(model_has_roles.model_id) as user_count'))
            ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereNotIn('roles.name', ['super_admin', 'Super Admin'])
            ->groupBy('roles.name')
            ->get();

        $totalUsers = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereNotIn('roles.name', ['super_admin', 'Super Admin'])
            ->distinct('model_has_roles.model_id')
            ->count('model_has_roles.model_id');

        $stats = [];

        $stats[] = Stat::make('Total User', $totalUsers)
            ->description('Total semua user aktif (kecuali Super Admin)')
            ->chart([5, 5, 5])
            ->color('success');

        foreach ($roleCounts as $role) {
            $stats[] = Stat::make(ucfirst($role->name), $role->user_count)
                ->description("Jumlah user dengan role {$role->name}")
                ->chart([5, 5, 5])
                ->icon('heroicon-o-users')
                ->color('primary');
        }

        return $stats;
    }
}

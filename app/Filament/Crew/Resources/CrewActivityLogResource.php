<?php

namespace App\Filament\Crew\Resources;

use App\Filament\Crew\Resources\CrewActivityLogResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Rmsramos\Activitylog\Resources\ActivitylogResource as BaseActivitylogResource;
use Rmsramos\Activitylog\Resources\ActivitylogResource\Pages\ViewActivitylog;
use Spatie\Activitylog\Models\Activity;

class CrewActivityLogResource extends BaseActivitylogResource implements HasShieldPermissions
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {

        return auth()->user()?->can('view_activity');
    }
    public static function canAccess(): bool
    {

        return auth()->user()?->can('view_activity');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
        ];
    }
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('causer.name')
                    ->label('Authors')
                    ->icon('heroicon-o-user')
                    ->color('info'),
                TextColumn::make('event')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('log_name')
                    ->label('Log')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->button()->color('success')
                    ->hidden(fn() => ! auth()->user()->can('view_any_activity')),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCrewActivityLogs::route('/'),
            'view' => ViewActivitylog::route('/{record}')
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->whereNotNull('causer_id');;
        $user = auth()->user();

        if ($user->hasAnyRole(['staff_crew', 'manager_crew'])) {
            return $query->whereHas('causer.roles', function ($q) {
                $q->whereIn('name', ['staff_crew', 'manager_crew']);
            });
        }

        return $query->where('causer_id', $user->id);
    }
}

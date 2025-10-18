<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Rmsramos\Activitylog\Resources\ActivitylogResource as BaseActivitylogResource;
use Rmsramos\Activitylog\Resources\ActivitylogResource\Pages\ViewActivitylog;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends BaseActivitylogResource
{
    protected static ?string $model = Activity::class;
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
                    ->label('Author')
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
            ->filters([
                SelectFilter::make('role')
                    ->label('Filter Berdasarkan Role')
                    ->native(false)
                    ->options(
                        Role::where('name', '!=', 'super_admin')
                            ->pluck('name', 'name')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (!filled($data['value'])) {
                            return;
                        }
                        $query->whereHas('causer.roles', function ($q) use ($data) {
                            $q->where('name', $data['value']);
                        });
                    }),
            ],)
            ->actions([
                Tables\Actions\ViewAction::make()->button()->color('success'),
                Tables\Actions\DeleteAction::make()->button()->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => ViewActivitylog::route('/{record}')
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        return $query->whereNotNull('causer_id');
    }
}

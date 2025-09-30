<?php

namespace App\Filament\StaffDocument\Resources;

use App\Filament\StaffDocument\Resources\ActivityLogResource\Pages;
use App\Filament\StaffDocument\Resources\ActivityLogResource\RelationManagers;
use Spatie\Activitylog\Models\Activity; // ← pakai bawaan Spatie
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Rmsramos\Activitylog\Resources\ActivitylogResource as BaseActivitylogResource;
use Rmsramos\Activitylog\Resources\ActivitylogResource\Pages\ViewActivitylog;

class ActivityLogResource extends BaseActivitylogResource
{
    protected static ?string $model = Activity::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('causer.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('event')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success', // hijau
                        'updated' => 'warning',    // biru
                        'deleted' => 'danger',  // merah
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
                //    Tables\Filters\SelectFilter::make('log_name')
                //        ->label('Log Name')
                //        ->options(
                //            Activity::query()
                //                ->select('log_name')
                //                ->distinct()
                //                ->pluck('log_name', 'log_name')
                //                ->toArray()
                //        )
                //        ->searchable()
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->button()->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('staff_crew')) {
            return $query->whereHas('causer.roles', fn($q) => $q->where('name', 'staff_crew'));
        }

        if ($user->hasRole('staff_document')) {
            return $query->whereHas('causer.roles', fn($q) => $q->where('name', 'staff_document'));
        }

        return $query->where('causer_id', $user->id);
    }
}

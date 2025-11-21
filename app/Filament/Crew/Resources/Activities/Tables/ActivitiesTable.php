<?php

namespace App\Filament\Crew\Resources\Activities\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                   TextColumn::make('description')
                    ->label('Author')
                    ->formatStateUsing(fn($record, $state) => $record->causer==null? 'sistem': $record->causer->name)
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
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->badgeColor('danger')
                    ->color('info')
                    ->label('Filter'),
            )
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Log')
                    ->native(false)
                    ->options(Activity::query()
                        ->distinct()
                        ->pluck('log_name', 'log_name')
                        ->filter()
                        ->toArray())
            ])
            ->recordActions([
                ViewAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}

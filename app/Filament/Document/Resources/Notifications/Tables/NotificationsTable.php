<?php

namespace App\Filament\Document\Resources\Notifications\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('notifiable_id', auth()->user()->id))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('data.title')
                    ->label('Title')
                    ->wrap(),
                TextColumn::make('data.body')
                    ->label('Body')
                    ->wrap(),
                IconColumn::make('read_at')
                    ->boolean()
                    ->label('Read'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')->color('success')->icon('heroicon-o-eye')->button()
                    ->visible(fn($record) => $record->data['actions'] == null ? false : true)
                    ->url(fn($record) => $record->data['actions'] == null ? null : url($record->data['actions'][0]['url'])),
                DeleteAction::make()->button()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

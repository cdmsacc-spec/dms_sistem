<?php

namespace App\Filament\Document\Resources\Notifications\Tables;

use Carbon\Carbon;
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
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('id')
                    ->label('Title')
                    ->wrap(),
                TextColumn::make('data.body')
                    ->label('Body')
                    ->wrap(),
                IconColumn::make('read_at')
                    ->boolean()
                    ->label('Read'),
                TextColumn::make('created_at')->dateTime('d-M-Y'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')->color('success')->icon('heroicon-o-eye')->button()
                    ->visible(fn($record) => $record->data['actions'] == null ? false : true)
                    ->action(function ($record, $livewire) {
                        $record->update([
                            'read_at' => now()->format('Y-m-d H:i:s'), // atau Carbon::now()
                        ]);

                        $url = $record->data['actions'][0]['url'] ?? null;
                        if ($url) {
                            $livewire->redirect($url);
                        }
                    }),
                DeleteAction::make()->button()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Document\Resources;

use App\Filament\Document\Resources\DocumentReminderResource\Pages;
use App\Models\DocumentReminder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;

class DocumentReminderResource extends Resource
{
    protected static ?string $model = DocumentReminder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Reminder';
    protected static ?string $modelLabel = 'Reminder';
    protected static ?string $pluralModelLabel = 'Reminder';
    protected static ?string $navigationGroup = 'Document Management';



    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultGroup('document.kapal.nama_kapal')
            ->groups([
                Group::make('document.kapal.nama_kapal')
                    ->label('')
                    ->collapsible(),
            ])
            ->groupingSettingsHidden()
            ->columns([
                Tables\Columns\TextColumn::make('document.kapal.nama_kapal')
                    ->label('Kapal')
                    ->color('success'),
                Tables\Columns\TextColumn::make('document.kapal.perusahaan.nama_perusahaan')
                    ->icon('heroicon-o-building-office')
                    ->label('Perusahaan')
                    ->color('success'),
                Tables\Columns\TextColumn::make('reminder_hari')
                    ->label('Reminder Tanggal')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn($state) => 'H- ' . $state),
                Tables\Columns\TextColumn::make('reminder_jam')
                    ->label('Reminder Jam')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn($state) => preg_replace('/\+\d+$/', '', $state)),

            ])
            ->filters([
                // ...
            ], layout: FiltersLayout::AboveContent)
            ->actions([])
            ->bulkActions([
              
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDocumentReminders::route('/'),
        ];
    }
}

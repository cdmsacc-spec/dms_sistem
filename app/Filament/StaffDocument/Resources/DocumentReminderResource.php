<?php

namespace App\Filament\StaffDocument\Resources;

use App\Filament\StaffDocument\Resources\DocumentReminderResource\Pages;
use App\Filament\StaffDocument\Resources\DocumentReminderResource\RelationManagers;
use App\Models\DocumentReminder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class DocumentReminderResource extends Resource
{
    protected static ?string $model = DocumentReminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Reminder';
    protected static ?string $modelLabel = 'Reminder';
    protected static ?string $pluralModelLabel = 'Reminder';
    protected static ?string $navigationGroup = 'Document Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_id')
                    ->relationship('document', 'nomor_dokumen')
                    ->required(),
                Forms\Components\DatePicker::make('reminder_hari')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultGroup('document.nomor_dokumen')
            ->groups([
                Group::make('document.nomor_dokumen')
                    ->label('document')
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDocumentReminders::route('/'),
        ];
    }
}

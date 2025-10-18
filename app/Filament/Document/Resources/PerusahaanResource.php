<?php

namespace App\Filament\Document\Resources;

use App\Filament\Document\Resources\PerusahaanResource\Pages;
use App\Filament\Document\Resources\PerusahaanResource\RelationManagers;
use App\Models\Perusahaan;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerusahaanResource extends Resource
{
    protected static ?string $model = Perusahaan::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Perusahaan';
    protected static ?string $modelLabel = 'Perusahaan';
    protected static ?string $pluralModelLabel = 'Perusahaan';
    protected static ?string $navigationGroup = 'Master Data';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->unique(ignorable: fn($record) => $record)
                    ->required(),
                TextInput::make('kode_perusahaan')
                    ->label('Kode Perusahaan')
                    ->unique(ignorable: fn($record) => $record)
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->unique(ignorable: fn($record) => $record)
                    ->required(),
                TextInput::make('telepon')
                    ->label('Telepon')
                    ->unique(ignorable: fn($record) => $record)
                    ->numeric()
                    ->required(),
                TextInput::make('npwp')
                    ->label('NPWP')
                    ->unique(ignorable: fn($record) => $record)
                    ->required(),
                Textarea::make('alamat')
                    ->label('Alamat')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                TextColumn::make('nama_perusahaan')
                    ->icon('heroicon-o-building-office')
                    ->color('success')
                    ->searchable(),
                TextColumn::make('email'),
                TextColumn::make('telepon')
                    ->badge()
                    ->color('success'),
                TextColumn::make('npwp'),
                TextColumn::make('alamat'),
                TextColumn::make('jumlah_kapal')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn($record) => $record->namaKapal()->count())
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make()
                        ->color('success'),
                ])
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
            'index' => Pages\ManagePerusahaans::route('/'),
        ];
    }
}
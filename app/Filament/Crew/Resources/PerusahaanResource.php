<?php

namespace App\Filament\Crew\Resources;

use App\Filament\Crew\Resources\PerusahaanResource\Pages;
use App\Filament\Crew\Resources\PerusahaanResource\RelationManagers;
use App\Models\Perusahaan;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Tables\Actions\MediaAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class PerusahaanResource extends Resource
{
    protected static ?string $model = Perusahaan::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Perusahaan';
    protected static ?string $modelLabel = 'Perusahaan';
    protected static ?string $pluralModelLabel = 'Perusahaan';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 10;
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
                TextInput::make('alamat')
                    ->label('Alamat')
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload File')
                    ->disk('public')
                    ->directory('perusahaan')
                    ->required()
                    ->columnSpanFull()
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get,) {
                        $namaPerusahaan = $get('nama_perusahaan');
                        return "{$namaPerusahaan}." .
                            $file->getClientOriginalExtension();
                    }),

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
                TextColumn::make('jumlah_kapal')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn($record) => $record->namaKapal()->count())
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->size('sm')
                    ->button()
                    ->color('warning')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file_path), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('priview')
                    ->label('Priview ')
                    ->size('sm')
                    ->button()
                    ->color('warning')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => $record->nama_perusahaan)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file_path)))
                    ->visible(function ($record) {
                        $path = $record->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),
                Tables\Actions\ViewAction::make()->button()->color('success'),
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\DeleteAction::make()->button(),
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

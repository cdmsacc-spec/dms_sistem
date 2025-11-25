<?php

namespace App\Filament\Resources\Perusahaans;

use App\Filament\Resources\Perusahaans\Pages\ManagePerusahaans;
use App\Models\Perusahaan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class PerusahaanResource extends Resource
{
    protected static ?string $model = Perusahaan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    protected static ?string $recordTitleAttribute = 'nama_perusahaan';
    protected static ?string $navigationLabel = "Perusahaan";
    protected static ?string $pluralModelLabel = "Perusahaan";
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $permissionGroup = 'User';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TextInput::make('telp')
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
                Textarea::make('keterangan')
                    ->columnSpanFull()
                    ->required(),
                FileUpload::make('file')
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_perusahaan')
                    ->label('Nama Perusahaan'),
                TextEntry::make('kode_perusahaan')
                    ->label('Kode Perusahaan'),
                TextEntry::make('email')
                    ->label('Email'),
                TextEntry::make('telp')
                    ->label('Telepon'),
                TextEntry::make('npwp')
                    ->label('NPWP'),
                TextEntry::make('alamat')
                    ->label('Alamat'),
                TextEntry::make('keterangan')
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama_perusahaan')
                    ->icon('heroicon-o-building-office')
                    ->color('success')
                    ->searchable(),
                TextColumn::make('email'),
                TextColumn::make('telp')
                    ->badge()
                    ->color('success'),
                TextColumn::make('npwp'),
                TextColumn::make('alamat'),
                TextColumn::make('jumlah_kapal')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn($record) => $record->kapal()->count())
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('download')
                    ->size('sm')
                    ->button()
                    ->color('info')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('Preview')
                    ->label('Preview')
                    ->size('sm')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('full')
                    ->button()
                    ->color('info')
                    ->modalHeading(fn($record) => $record->nama_perusahaan)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file)))
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),

                ViewAction::make()->button(),
                EditAction::make()->button(),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePerusahaans::route('/'),
        ];
    }
}

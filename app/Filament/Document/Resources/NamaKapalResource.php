<?php

namespace App\Filament\Document\Resources;

use App\Filament\Document\Resources\NamaKapalResource\Pages;
use App\Filament\Document\Resources\NamaKapalResource\RelationManagers;
use App\Models\Lookup;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Tables\Actions\MediaAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class NamaKapalResource extends Resource
{
    protected static ?string $model = NamaKapal::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Kapal';
    protected static ?string $modelLabel = 'Kapal';
    protected static ?string $pluralModelLabel = 'Kapal';
    protected static ?string $navigationGroup = 'Master Data';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('perusahaan_id')
                    ->relationship('perusahaan', 'nama_perusahaan')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('jenis_kapal_id')
                    ->relationship('jenisKapal', 'nama_jenis')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('nama_kapal')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status_certified')
                    ->options(
                        Lookup::where('kategori', 'Certified')
                            ->pluck('value', 'value')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('tahun_kapal')
                    ->numeric(),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload File')
                    ->disk('public')
                    ->directory('kapal')
                    ->required()
                    ->columnSpanFull()
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get,) {
                        $perusahaanId   = $get('perusahaan_id');
                        $namaPerusahaan = Perusahaan::find($perusahaanId)?->nama_perusahaan;
                        $namaKapal      = $get('nama_kapal');

                        return "{$namaPerusahaan}-{$namaKapal}." .
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
                Tables\Columns\TextColumn::make('nama_kapal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenisKapal.nama_jenis')
                    ->icon('heroicon-o-queue-list')
                    ->searchable(),
                Tables\Columns\TextColumn::make('perusahaan.nama_perusahaan')
                    ->icon('heroicon-o-building-office')
                    ->color('success')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_certified')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_kapal')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->size('sm')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file_path), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('priview')
                    ->label('Priview‎ ‎ ')
                    ->size('sm')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => $record->nama_kapal)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file_path)))
                    ->visible(function ($record) {
                        $path = $record->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),
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
            'index' => Pages\ManageNamaKapals::route('/'),
        ];
    }
}

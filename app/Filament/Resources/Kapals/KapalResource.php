<?php

namespace App\Filament\Resources\Kapals;

use App\Filament\Resources\Kapals\Pages\ManageKapals;
use App\Models\Kapal;
use App\Models\Lookup;
use App\Models\Perusahaan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class KapalResource extends Resource
{
    protected static ?string $model = Kapal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PaperAirplane;

    protected static ?string $recordTitleAttribute = 'nama_kapal';
    protected static ?string $navigationLabel = "Kapal";
    protected static ?string $pluralModelLabel = "Kapal";
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $permissionGroup = 'User';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('id_perusahaan')
                    ->relationship('perusahaan', 'nama_perusahaan')
                    ->native(false)
                    ->searchable()
                    ->placeholder('')
                    ->preload()
                    ->required(),
                Select::make('id_jenis_kapal')
                    ->relationship('jenisKapal', 'nama_jenis')
                    ->native(false)
                    ->searchable()
                    ->placeholder('')
                    ->preload()
                    ->required(),
                Select::make('id_wilayah')
                    ->relationship('wilayahOperasional', 'nama_wilayah')
                    ->native(false)
                    ->searchable()
                    ->placeholder('')
                    ->preload(),
                TextInput::make('nama_kapal')
                    ->required()
                    ->maxLength(255),
                Select::make('status_certified')
                    ->options(
                        Lookup::where('type', 'sertified_kapal')
                            ->pluck('name', 'name')
                            ->toArray()
                    )
                    ->placeholder('')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                TextInput::make('tahun_kapal')
                    ->numeric(),
                Textarea::make('keterangan')
                    ->columnSpanFull()
                    ->nullable(),
                FileUpload::make('file')
                    ->label('Upload File')
                    ->disk('public')
                    ->directory('kapal')
                    ->required()
                    ->downloadable()
                    ->columnSpanFull()
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get,) {
                        $perusahaanId   = $get('id_perusahaan');
                        $namaPerusahaan = Perusahaan::find($perusahaanId)?->nama_perusahaan;
                        $namaKapal      = $get('nama_kapal');

                        return "{$namaPerusahaan}-{$namaKapal}." .
                            $file->getClientOriginalExtension();
                    }),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_kapal'),
                TextEntry::make('tahun_kapal'),
                TextEntry::make('status_certified'),
                TextEntry::make('perusahaan.nama_perusahaan'),
                TextEntry::make('wilayahOperasional.nama_wilayah'),
                TextEntry::make('file')
                    ->label('File Dokumen')
                    ->icon('heroicon-o-document-text')
                    ->badge()
                    ->getStateUsing(
                        function ($record) {
                            return  $record->file ?? null;
                        }
                    )
                    ->formatStateUsing(
                        fn($state) =>
                        $state ? 'Document File' : 'Tidak ada file'
                    )
                    ->color(
                        fn($state) =>
                        $state ? 'info' : 'danger'
                    )
                    ->url(
                        fn($state) =>
                        asset('storage/' . $state),
                        shouldOpenInNewTab: true
                    ),
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('keterangan')
                            ->columnSpanFull(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_kapal')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama_kapal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tahun_kapal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status_certified')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('perusahaan.nama_perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenisKapal.nama_jenis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('wilayahOperasional.nama_wilayah')
                    ->searchable()
                    ->sortable(),
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
                    ->modalHeading(fn($record) => $record->nama_kapal)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file)))
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),

                ViewAction::make()->button()->modalAlignment(Alignment::Center)->modalIcon('heroicon-o-paper-airplane'),
                EditAction::make()->button(),
                DeleteAction::make()->button()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKapals::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources\WilayahOperasionals;

use App\Filament\Resources\WilayahOperasionals\Pages\ManageWilayahOperasionals;
use App\Models\WilayahOperasional;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class WilayahOperasionalResource extends Resource
{
    protected static ?string $model = WilayahOperasional::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?string $recordTitleAttribute = 'nama_wilayah';
    protected static ?string $navigationLabel = "Wilayah Operasional";
    protected static ?string $pluralModelLabel = "Wilayah Operasional";
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $permissionGroup = 'User';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_wilayah')
                    ->required()
                    ->columnSpan(1),
                TextInput::make('kode_wilayah')
                    ->required()
                    ->columnSpan(1),
                Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),

                Section::make('tanda_tangan')
                    ->columnSpanFull()
                    ->columns(['sm' => 1, 'md' => 2, 'lg' => 2, 'xl' => 3])
                    ->schema([
                        Textinput::make('ttd_dibuat')->required(),
                        Textinput::make('ttd_diperiksa')->required(),
                        Textinput::make('ttd_diketahui_1')->required(),
                        Textinput::make('ttd_diketahui_2')->required(),
                        Textinput::make('ttd_disetujui_1')->required(),
                        Textinput::make('ttd_disetujui_2')->required(),
                    ])
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_wilayah'),
                TextEntry::make('kode_wilayah'),
                TextEntry::make('deskripsi'),
                Section::make('tanda_tangan')
                    ->columnSpanFull()
                    ->columns(['sm' => 1, 'md' => 2, 'lg' => 2, 'xl' => 3])
                    ->schema([
                        TextEntry::make('ttd_dibuat'),
                        TextEntry::make('ttd_diperiksa'),
                        TextEntry::make('ttd_diketahui_1'),
                        TextEntry::make('ttd_diketahui_2'),
                        TextEntry::make('ttd_disetujui_1'),
                        TextEntry::make('ttd_disetujui_2'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_wilayah')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama_wilayah')
                    ->searchable(),
                TextColumn::make('kode_wilayah')
                    ->searchable(),
                TextColumn::make('deskripsi')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
            'index' => ManageWilayahOperasionals::route('/'),
        ];
    }
}

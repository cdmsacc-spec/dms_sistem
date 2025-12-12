<?php

namespace App\Filament\Resources\JenisKapals;

use App\Filament\Resources\JenisKapals\Pages\ManageJenisKapals;
use App\Models\JenisKapal;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class JenisKapalResource extends Resource
{
    protected static ?string $model = JenisKapal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::FolderOpen;

    protected static ?string $recordTitleAttribute = 'nama_jenis';
    protected static ?string $navigationLabel = "Jenis Kapal";
    protected static ?string $pluralModelLabel = "Jenis Kapal";
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $permissionGroup = 'User';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_jenis')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_jenis')
                    ->label('Nama Jenis'),
                TextEntry::make('deskripsi')
                    ->label('Deskripsi'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_jenis')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama_jenis')
                    ->label('Nama Jenis')
                    ->searchable(),
                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->button(),
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
            'index' => ManageJenisKapals::route('/'),
        ];
    }
}

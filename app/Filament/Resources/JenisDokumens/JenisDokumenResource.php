<?php

namespace App\Filament\Resources\JenisDokumens;

use App\Filament\Resources\JenisDokumens\Pages\ManageJenisDokumens;
use App\Models\JenisDokumen;
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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class JenisDokumenResource extends Resource
{
    protected static ?string $model = JenisDokumen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected static ?string $recordTitleAttribute = 'nama_jenis';
    protected static ?string $navigationLabel = "Jenis Dokumen";
    protected static ?string $pluralModelLabel = "Jenis Dokumen";
    protected static string | UnitEnum | null $navigationGroup = 'Dokumen Management';
    protected static ?string $permissionGroup = 'User';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_jenis')
                    ->required(),
                TextInput::make('deskripsi')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('nama_jenis'),
                TextEntry::make('deskripsi'),
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
                    ->searchable(),
                TextColumn::make('deskripsi'),
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
            'index' => ManageJenisDokumens::route('/'),
        ];
    }
}

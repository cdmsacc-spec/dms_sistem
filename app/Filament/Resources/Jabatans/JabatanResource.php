<?php

namespace App\Filament\Resources\Jabatans;

use App\Filament\Resources\Jabatans\Pages\ManageJabatans;
use App\Models\Jabatan;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class JabatanResource extends Resource
{
    protected static ?string $model = Jabatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;

    protected static ?string $recordTitleAttribute = 'nama_jabatan';
    protected static ?string $navigationLabel = "Jabatan";
    protected static ?string $pluralModelLabel = "Jabatan";
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $permissionGroup = 'User';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_jabatan')
                    ->required(),
                TextInput::make('kode_jabatan')
                    ->required(),
                Select::make('golongan')
                    ->native(false)
                    ->required()
                    ->searchable()
                    ->placeholder('')
                    ->preload()
                    ->options([
                        'perwira' => 'Perwira',
                        'non-perwira' => 'Non-Perwira'
                    ]),
                Select::make('devisi')
                    ->native(false)
                    ->required()
                    ->searchable()
                    ->placeholder('')
                    ->preload()
                    ->options([
                        'Deck' => 'Deck',
                        'Mesin' => 'Mesin'
                    ]),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_jabatan')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama_jabatan')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('golongan')
                    ->searchable(),
                TextColumn::make('devisi')
                    ->searchable(),
                TextColumn::make('kode_jabatan')
                    ->badge()
                    ->color('info')
                    ->searchable(),
            ])
            ->filters([])
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
            'index' => ManageJabatans::route('/'),
        ];
    }
}

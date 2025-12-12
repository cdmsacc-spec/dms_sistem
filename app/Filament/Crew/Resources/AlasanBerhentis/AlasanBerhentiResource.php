<?php

namespace App\Filament\Crew\Resources\AlasanBerhentis;

use App\Filament\Crew\Resources\AlasanBerhentis\Pages\ManageAlasanBerhentis;
use App\Models\AlasanBerhenti;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AlasanBerhentiResource extends Resource
{
    protected static ?string $model = AlasanBerhenti::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_alasan';
    protected static ?string $navigationLabel = "Alasan Berhenti";
    protected static ?string $pluralModelLabel = "Alasan Berhenti";
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $permissionGroup = 'User';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('nama_alasan')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_alasan')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama_alasan')
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
            'index' => ManageAlasanBerhentis::route('/'),
        ];
    }
}

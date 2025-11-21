<?php

namespace App\Filament\Crew\Resources\CrewMutasis;

use App\Filament\Crew\Resources\CrewMutasis\Pages\CreateCrewMutasi;
use App\Filament\Crew\Resources\CrewMutasis\Pages\EditCrewMutasi;
use App\Filament\Crew\Resources\CrewMutasis\Pages\ListCrewMutasis;
use App\Filament\Crew\Resources\CrewMutasis\Pages\ViewCrewMutasi;
use App\Filament\Crew\Resources\CrewMutasis\RelationManagers\MutasiRelationManager;
use App\Filament\Crew\Resources\CrewMutasis\Schemas\CrewMutasiForm;
use App\Filament\Crew\Resources\CrewMutasis\Schemas\CrewMutasiInfolist;
use App\Filament\Crew\Resources\CrewMutasis\Tables\CrewMutasisTable;
use App\Models\Crew;
use App\Models\CrewMutasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrewMutasiResource extends Resource
{
    protected static ?string $model = Crew::class;

    protected static string|BackedEnum|null $navigationIcon = null;
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = "Mutasi promosi";
    protected static ?string $pluralModelLabel = "Mutasi promosi";
    protected static string | UnitEnum | null $navigationGroup = 'Crew Management';
    protected static ?string $permissionGroup = 'Crew';

    public static function form(Schema $schema): Schema
    {
        return CrewMutasiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CrewMutasiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrewMutasisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
           MutasiRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrewMutasis::route('/'),
            'create' => CreateCrewMutasi::route('/create'),
            'view' => ViewCrewMutasi::route('/{record}'),
            'edit' => EditCrewMutasi::route('/{record}/mutasi-promosi'),
        ];
    }
}

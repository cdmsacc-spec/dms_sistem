<?php

namespace App\Filament\Crew\Resources\CrewSignoffs;

use App\Filament\Crew\Resources\CrewSignoffs\Pages\CreateCrewSignoff;
use App\Filament\Crew\Resources\CrewSignoffs\Pages\EditCrewSignoff;
use App\Filament\Crew\Resources\CrewSignoffs\Pages\ListCrewSignoffs;
use App\Filament\Crew\Resources\CrewSignoffs\RelationManagers\SignoffRelationManager;
use App\Filament\Crew\Resources\CrewSignoffs\Schemas\CrewSignoffForm;
use App\Filament\Crew\Resources\CrewSignoffs\Tables\CrewSignoffsTable;
use App\Models\Crew;
use App\Models\CrewSignoff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrewSignoffResource extends Resource
{
    protected static ?string $model = Crew::class;

       protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 8;
    protected static ?string $navigationLabel = "Sign off";
    protected static ?string $pluralModelLabel = "Sign off";
    protected static string | UnitEnum | null $navigationGroup = 'Crew Management';
    protected static ?string $permissionGroup = 'User';
    public static function form(Schema $schema): Schema
    {
        return CrewSignoffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrewSignoffsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
           SignoffRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrewSignoffs::route('/'),
            'create' => CreateCrewSignoff::route('/create'),
            'edit' => EditCrewSignoff::route('/{record}/signoff'),
        ];
    }
}

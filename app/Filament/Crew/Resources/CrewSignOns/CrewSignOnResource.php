<?php

namespace App\Filament\Crew\Resources\CrewSignOns;

use App\Filament\Crew\Resources\CrewSignOns\Pages\CreateCrewSignOn;
use App\Filament\Crew\Resources\CrewSignOns\Pages\EditCrewSignOn;
use App\Filament\Crew\Resources\CrewSignOns\Pages\ListCrewSignOns;
use App\Filament\Crew\Resources\CrewSignOns\Pages\ViewCrewSignOn;
use App\Filament\Crew\Resources\CrewSignOns\RelationManagers\SigonRelationManager;
use App\Filament\Crew\Resources\CrewSignOns\Schemas\CrewSignOnForm;
use App\Filament\Crew\Resources\CrewSignOns\Schemas\CrewSignOnInfolist;
use App\Filament\Crew\Resources\CrewSignOns\Tables\CrewSignOnsTable;
use App\Models\Crew;
use App\Models\CrewSignOn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrewSignOnResource extends Resource
{
    protected static ?string $model = Crew::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = "Sign on";
    protected static ?string $pluralModelLabel = "Sign on";
    protected static string | UnitEnum | null $navigationGroup = 'Crew Management';
    protected static ?string $permissionGroup = 'User';

    public static function form(Schema $schema): Schema
    {
        return CrewSignOnForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CrewSignOnInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrewSignOnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
           SigonRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrewSignOns::route('/'),
            'create' => CreateCrewSignOn::route('/create'),
            'view' => ViewCrewSignOn::route('/{record}'),
            'edit' => EditCrewSignOn::route('/{record}/signon'),
        ];
    }
}

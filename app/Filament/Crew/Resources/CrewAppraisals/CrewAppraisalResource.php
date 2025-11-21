<?php

namespace App\Filament\Crew\Resources\CrewAppraisals;

use App\Filament\Crew\Resources\CrewAppraisals\Pages\CreateCrewAppraisal;
use App\Filament\Crew\Resources\CrewAppraisals\Pages\EditCrewAppraisal;
use App\Filament\Crew\Resources\CrewAppraisals\Pages\ListCrewAppraisals;
use App\Filament\Crew\Resources\CrewAppraisals\Pages\ViewCrewAppraisal;
use App\Filament\Crew\Resources\CrewAppraisals\RelationManagers\AppraisalRelationManager;
use App\Filament\Crew\Resources\CrewAppraisals\Schemas\CrewAppraisalForm;
use App\Filament\Crew\Resources\CrewAppraisals\Schemas\CrewAppraisalInfolist;
use App\Filament\Crew\Resources\CrewAppraisals\Tables\CrewAppraisalsTable;
use App\Models\CrewAppraisal;
use App\Models\CrewKontrak;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrewAppraisalResource extends Resource
{
    protected static ?string $model = CrewKontrak::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = "Appraisal";
    protected static ?string $pluralModelLabel = "Appraisal";
    protected static string | UnitEnum | null $navigationGroup = 'Crew Management';
    protected static ?string $permissionGroup = 'User';

    public static function form(Schema $schema): Schema
    {
        return CrewAppraisalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CrewAppraisalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrewAppraisalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AppraisalRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrewAppraisals::route('/'),
            'create' => CreateCrewAppraisal::route('/create'),
            'view' => ViewCrewAppraisal::route('/{record}'),
            'edit' => EditCrewAppraisal::route('/{record}/edit'),
        ];
    }
}

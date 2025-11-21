<?php

namespace App\Filament\Crew\Resources\CrewInterviews;

use App\Filament\Crew\Resources\CrewInterviews\Pages\CreateCrewInterview;
use App\Filament\Crew\Resources\CrewInterviews\Pages\EditCrewInterview;
use App\Filament\Crew\Resources\CrewInterviews\Pages\ListCrewInterviews;
use App\Filament\Crew\Resources\CrewInterviews\Pages\ViewCrewInterview;
use App\Filament\Crew\Resources\CrewInterviews\Schemas\CrewInterviewForm;
use App\Filament\Crew\Resources\CrewInterviews\Schemas\CrewInterviewInfolist;
use App\Filament\Crew\Resources\CrewInterviews\Tables\CrewInterviewsTable;
use App\Models\Crew;
use App\Models\CrewInterview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrewInterviewResource extends Resource
{
    protected static ?string $model = Crew::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'summary';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = "Interview";
    protected static ?string $pluralModelLabel = "Interview";
    protected static string | UnitEnum | null $navigationGroup = 'Crew Management';
    public static function getPermissionPrefix(): ?string
    {
        return 'crew';
    }

    public static function form(Schema $schema): Schema
    {
        return CrewInterviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrewInterviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrewInterviews::route('/'),
            'create' => CreateCrewInterview::route('/create'),
            'edit' => EditCrewInterview::route('/{record}/interview'),
        ];
    }
}

<?php

namespace App\Filament\Crew\Resources\CrewDrafts;

use App\Filament\Crew\Resources\CrewDrafts\Pages\CreateCrewDraft;
use App\Filament\Crew\Resources\CrewDrafts\Pages\EditCrewDraft;
use App\Filament\Crew\Resources\CrewDrafts\Pages\ListCrewDrafts;
use App\Filament\Crew\Resources\CrewDrafts\Schemas\CrewDraftForm;
use App\Filament\Crew\Resources\CrewDrafts\Tables\CrewDraftsTable;
use App\Models\Crew;
use App\Models\CrewDraft;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrewDraftResource extends Resource
{
    protected static ?string $model = Crew::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nama_crew';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = "Draft";
    protected static ?string $pluralModelLabel = "Draft";
    protected static string | UnitEnum | null $navigationGroup = 'Crew Management';

    public static function form(Schema $schema): Schema
    {
        return CrewDraftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrewDraftsTable::configure($table);
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
            'index' => ListCrewDrafts::route('/'),
            'create' => CreateCrewDraft::route('/create'),
            'edit' => EditCrewDraft::route('/{record}/edit'),
        ];
    }
}

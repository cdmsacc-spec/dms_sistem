<?php

namespace App\Filament\Crew\Resources\AllCrews;

use App\Filament\Crew\Resources\AllCrews\Pages\CreateAllCrew;
use App\Filament\Crew\Resources\AllCrews\Pages\EditAllCrew;
use App\Filament\Crew\Resources\AllCrews\Pages\HistoryInterview;
use App\Filament\Crew\Resources\AllCrews\Pages\HistoryMutasiPromosi;
use App\Filament\Crew\Resources\AllCrews\Pages\HistorySignOff;
use App\Filament\Crew\Resources\AllCrews\Pages\HistorySignOn;
use App\Filament\Crew\Resources\AllCrews\Pages\ListAllCrews;
use App\Filament\Crew\Resources\AllCrews\Pages\Detail\DetailKontrak;
use App\Filament\Crew\Resources\AllCrews\Pages\ViewAllCrew;
use App\Filament\Crew\Resources\AllCrews\RelationManagers\DokumenRelationManager;
use App\Filament\Crew\Resources\AllCrews\RelationManagers\ExperienceRelationManager;
use App\Filament\Crew\Resources\AllCrews\RelationManagers\SertifikatRelationManager;
use App\Filament\Crew\Resources\AllCrews\Schemas\AllCrewForm;
use App\Filament\Crew\Resources\AllCrews\Schemas\AllCrewInfolist;
use App\Filament\Crew\Resources\AllCrews\Tables\AllCrewsTable;
use App\Models\Crew;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AllCrewResource extends Resource
{
    protected static ?string $model = Crew::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $recordTitleAttribute = 'nama_crew';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = "All Crew";
    protected static ?string $pluralModelLabel = "All Crew";
    protected static string | UnitEnum | null $navigationGroup = 'Crew Management';

    public static function form(Schema $schema): Schema
    {
        return AllCrewForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AllCrewInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AllCrewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DokumenRelationManager::class,
            SertifikatRelationManager::class,
            ExperienceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAllCrews::route('/'),
            'create' => CreateAllCrew::route('/create'),
            'view' => ViewAllCrew::route('/{record}'),
            'edit' => EditAllCrew::route('/{record}/edit'),
            'history-interview' => HistoryInterview::route('/{record}/history-interview'),
            'history-signon' => HistorySignOn::route('/{record}/history-signon'),
            'history-mutasi' => HistoryMutasiPromosi::route('/{record}/history-mutasi'),
            'history-signoff' => HistorySignOff::route('/{record}/history-signoff'),
            'detail_kontrak' => DetailKontrak::route('/{record}/detail_kontak'),





        ];
    }
}

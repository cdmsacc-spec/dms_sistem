<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\CreateActivities;
use App\Filament\Resources\Activities\Pages\EditActivities;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Pages\ViewActivities;
use App\Filament\Resources\Activities\Schemas\ActivitiesForm;
use App\Filament\Resources\Activities\Schemas\ActivitiesInfolist;
use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Spatie\Activitylog\Models\Activity;


class ActivitiesResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Eye;

    protected static ?string $recordTitleAttribute = 'Detail';
    protected static ?string $navigationLabel = "Activity Log";
    protected static ?string $pluralModelLabel = "Activity Log";
    protected static string | UnitEnum | null $navigationGroup = 'Logs';
    protected static ?string $permissionGroup = 'User';

    public static function form(Schema $schema): Schema
    {
        return ActivitiesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivitiesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure(
            $table
        );
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
            'index' => ListActivities::route('/'),
            'create' => CreateActivities::route('/create'),
            'view' => ViewActivities::route('/{record}'),
            'edit' => EditActivities::route('/{record}/edit'),
        ];
    }
}

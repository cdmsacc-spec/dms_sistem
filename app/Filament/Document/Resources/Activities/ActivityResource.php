<?php

namespace App\Filament\Document\Resources\Activities;

use App\Filament\Document\Resources\Activities\Pages\CreateActivity;
use App\Filament\Document\Resources\Activities\Pages\EditActivity;
use App\Filament\Document\Resources\Activities\Pages\ListActivities;
use App\Filament\Document\Resources\Activities\Pages\ViewActivity;
use App\Filament\Document\Resources\Activities\Schemas\ActivityForm;
use App\Filament\Document\Resources\Activities\Schemas\ActivityInfolist;
use App\Filament\Document\Resources\Activities\Tables\ActivitiesTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityResource extends Resource
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
        return ActivityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
         return ActivitiesTable::configure(
        $table->modifyQueryUsing(function ($query) {
            $userIds = User::role([
                'staff_dokumen',
                'manager_dokumen',
                'operation_dokumen',
            ])->pluck('id');

            $query->where('causer_type', User::class)
                  ->whereIn('causer_id', $userIds);
        })
    );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'view' => ViewActivity::route('/{record}'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }
}

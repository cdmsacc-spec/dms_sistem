<?php

namespace App\Filament\Document\Resources\Dokumens;

use App\Filament\Document\Resources\Dokumens\Pages\CreateDokumen;
use App\Filament\Document\Resources\Dokumens\Pages\DokumenAudit;
use App\Filament\Document\Resources\Dokumens\Pages\EditDokumen;
use App\Filament\Document\Resources\Dokumens\Pages\ListDokumens;
use App\Filament\Document\Resources\Dokumens\Pages\ViewDokumen;
use App\Filament\Document\Resources\Dokumens\RelationManagers\HistoryDokumenRelationManager;
use App\Filament\Document\Resources\Dokumens\Schemas\DokumenForm;
use App\Filament\Document\Resources\Dokumens\Schemas\DokumenInfolist;
use App\Filament\Document\Resources\Dokumens\Tables\DokumensTable;
use App\Models\Dokumen;
use App\Models\HistoryDokumen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tapp\FilamentAuditing\RelationManagers\AuditsRelationManager;
use UnitEnum;

class DokumenResource extends Resource
{
    protected static ?string $model = Dokumen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'penerbit';
    protected static ?string $navigationLabel = "Dokumen";
    protected static ?string $pluralModelLabel = "Dokumen";
    protected static string | UnitEnum | null $navigationGroup = 'Dokumen Management';
    protected static ?string $permissionGroup = 'User';

    public static function form(Schema $schema): Schema
    {
        return DokumenForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DokumenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DokumensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HistoryDokumenRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDokumens::route('/'),
            'create' => CreateDokumen::route('/create'),
            'view' => ViewDokumen::route('/{record}'),
            'edit' => EditDokumen::route('/{record}/edit'),
            'audit' => DokumenAudit::route('/audit/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->leftJoin(\DB::raw(
                'LATERAL (
                SELECT de.tanggal_expired
                FROM history_dokumens de
                WHERE de.id_dokumen = dokumens.id
                ORDER BY de.id DESC
                LIMIT 1
            ) AS latest_de'
            ), \DB::raw('true'), '=', \DB::raw('true'))
            ->addSelect('dokumens.*')
            ->addSelect(\DB::raw('(latest_de.tanggal_expired - CURRENT_DATE) AS jarak_hari'));
    }
}

<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\Pages\ManageLookups;
use App\Models\Lookup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LookupResource extends Resource
{
    protected static ?string $model = Lookup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'type';
    protected static ?string $navigationLabel = "Lookup";
    protected static ?string $pluralModelLabel = "Lookup";
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $permissionGroup = 'Lookup';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->native(false)
                    ->placeholder('')
                    ->searchable()
                    ->options([
                        'sertified_kapal' => 'sertified_kapal',
                        'nomor_versi_dokumen' => 'nomor_versi_dokumen',
                        'interview' => 'interview',
                        //'signon' => 'signon',
                        //'mutasi/promosi' => 'mutasi/promosi',
                        'sign_off' => 'sign_off',
                        'template_form' => 'template_form',
                    ])
                    ->required(),
                Select::make('code')
                    ->native(false)
                    ->placeholder('')
                    ->searchable()
                    ->options(function ($get) {
                        $type = $get('type');
                        return match ($type) {
                            'sertified_kapal' => ['sk' => 'sk'],
                            'nomor_versi_dokumen' => ['sign_on' => 'sign_on'],
                            'interview' => ['crewing' => 'crewing', 'user(operations/technique)' => 'user(operations/technique)', 'staff_rekrutmen' => 'staff_rekrutmen', 'manager_crew' => 'manager_crew'],
                            //'signon' => ['dibuat_oleh' => 'dibuat_oleh', 'diperiksa_oleh' => 'diperiksa_oleh', 'diketahui_oleh_1' => 'diketahui_oleh_1', 'diketahui_oleh_2' => 'diketahui_oleh_2', 'disetujui_oleh_1' => 'disetujui_oleh_1', 'disetujui_oleh_2' => 'disetujui_oleh_2'],
                            //'mutasi/promosi' => ['dibuat_oleh' => 'dibuat_oleh', 'diketahui_oleh' => 'diketahui_oleh', 'disetujui_oleh' => 'disetujui_oleh'],
                            'sign_off' => ['crewing_manager' => 'crewing_manager', 'direktur' => 'direktur'],
                            'template_form' => ['template_interview' => 'template_interview', 'template_sign_on' => 'template_sign_on', 'template_sign_off' => 'template_sign_off', 'template_mutasi_promosi', 'template_mutasi_promosi'],
                            default => [],
                        };
                    })
                    ->required(),
                TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('type')
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button(),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLookups::route('/'),
        ];
    }
}

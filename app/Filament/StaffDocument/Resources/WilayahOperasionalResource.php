<?php

namespace App\Filament\StaffDocument\Resources;

use App\Filament\StaffDocument\Resources\WilayahOperasionalResource\Pages;
use App\Filament\StaffDocument\Resources\WilayahOperasionalResource\RelationManagers;
use App\Models\WilayahOperasional;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WilayahOperasionalResource extends Resource
{

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    protected static ?string $model = WilayahOperasional::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Wilayah Operasional';
    protected static ?string $modelLabel = 'Wilayah Operasional';
    protected static ?string $pluralModelLabel = 'Wilayah Operasional';
    protected static ?string $navigationGroup = 'Master Data';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_wilayah')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_wilayah')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_wilayah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode_wilayah')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make()
                        ->color('success'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWilayahOperasionals::route('/'),
        ];
    }
}

<?php

namespace App\Filament\StaffCrew\Resources\CrewCandidateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class CrewExperienceRelationManager extends RelationManager
{
    protected static string $relationship = 'crewExperience';
    protected static bool $isLazy = false;
    protected static ?string $title = 'Experience';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->crewExperience->count();
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_kapal')
                    ->required()
                    ->columns(1),
                Forms\Components\TextInput::make('nama_perusahaan')
                    ->required()
                    ->columns(1),
                Forms\Components\TextInput::make('bendera')
                    ->required()
                    ->columns(1),
                Forms\Components\TextInput::make('posisi')
                    ->required()
                    ->columns(1),
                Forms\Components\TextInput::make('gt_kw')
                    ->label('GT / KW')
                    ->required()
                    ->columns(1),
                Forms\Components\TextInput::make('tipe_kapal')
                    ->required()
                    ->columns(1),
                Forms\Components\DatePicker::make('periode_awal')
                    ->prefixIcon('heroicon-m-calendar')
                    ->required()
                    ->native(false)
                    ->columns(1),
                Forms\Components\DatePicker::make('periode_akhir')
                    ->prefixIcon('heroicon-m-calendar')
                    ->required()
                    ->native(false)
                    ->columns(1),
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('posisi')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->heading('')
            ->columns([
                Tables\Columns\TextColumn::make('nama_perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_kapal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bendera')
                    ->searchable(),
                Tables\Columns\TextColumn::make('posisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe_kapal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('periode_awal'),
                Tables\Columns\TextColumn::make('periode_akhir'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Experience')
                    ->modalHeading('Add Data Experience'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

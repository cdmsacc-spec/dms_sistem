<?php

namespace App\Filament\Crew\Resources\CrewAppraisals\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppraisalRelationManager extends RelationManager
{
    protected static string $relationship = 'appraisal';
    protected $listeners = ['refresh' => '$refresh'];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('aprraiser')
                    ->columnSpanFull()
                    ->required(),
                Select::make('nilai')
                    ->placeholder('')
                    ->native(false)
                    ->options([
                        100 => "Sangat Memuaskan",
                        70 => 'Memuaskan',
                        50 => 'Cukup Memuaskan',
                        25 => 'Tidak Memuaskan',
                    ])
                    ->columnSpanFull()
                    ->required(),
                Textarea::make('keterangan')
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('aprraiser'),
                TextColumn::make('created_at')
                    ->sortable()
                    ->label('Tanggal'),
                TextColumn::make('nilai')
                    ->sortable()
                    ->description(fn($state) => 'bobot nilai ' . $state)
                    ->formatStateUsing(
                        fn($state) =>
                        $state == 100 ? 'Sangat Memuaskan' : ($state == 75 ? 'Memuaskan' : ($state == 50 ? 'Cukup Memuaskan' : ($state == 25 ? 'Tidak Memuaskan' : '-')))
                    ),
                TextColumn::make('keterangan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                EditAction::make()->button()
                    ->modalIcon('heroicon-o-printer')
                    ->modalHeading('Edit Appraisal')
                    ->modalAlignment('center')
                    ->modalWidth(Width::Medium),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

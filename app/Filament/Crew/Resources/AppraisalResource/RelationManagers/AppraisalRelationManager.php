<?php

namespace App\Filament\Crew\Resources\AppraisalResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalRelationManager extends RelationManager
{
    protected static string $relationship = 'appraisal';
    protected $listeners = ['refresh' => '$refresh'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('appraiser')
                            ->required(),
                        Forms\Components\Select::make('nilai')
                            ->native(false)
                            ->options([
                                100 => "Sangat Memuaskan",
                                70 => 'Memuaskan',
                                50 => 'Cukup Memuaskan',
                                25 => 'Tidak Memuaskan',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('keterangan')
                            ->columnSpanFull()
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('nilai')
            ->heading('')
            ->columns([
                Tables\Columns\TextColumn::make('appraiser'),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('nilai')
                    ->sortable()
                    ->description(fn($state) => 'bobot nilai ' . $state)
                    ->formatStateUsing(
                        fn($state) =>
                        $state == 100 ? 'Sangat Memuaskan' : ($state == 75 ? 'Memuaskan' : ($state == 50 ? 'Cukup Memuaskan' : ($state == 25 ? 'Tidak Memuaskan' : '-')))
                    ),
                Tables\Columns\TextColumn::make('keterangan'),
            ])
            ->filters([
                //
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

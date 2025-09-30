<?php

namespace App\Filament\StaffCrew\Resources\CrewInterviewResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CrewInterviewRelationManager extends RelationManager
{
    protected static string $relationship = 'crewInterview';
    protected $listeners = ['refresh' => '$refresh'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->prefixIcon('heroicon-m-calendar')
                    ->native(false)
                    ->required(),
                Forms\Components\Textarea::make('hasil_interviewe1'),
                Forms\Components\Textarea::make('hasil_interviewe2'),
                Forms\Components\Textarea::make('hasil_interviewe3'),
                Forms\Components\Textarea::make('sumary'),
                Forms\Components\Textarea::make('keterangan'),
                Forms\Components\FileUpload::make('file_path')
                    ->columnSpan(1)
                    ->disk('public')
                    ->preserveFilenames()
                    ->directory('crew/interview')
                    ->required()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data interview ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->heading('')
            ->recordTitleAttribute('tanggal')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->icon('heroicon-m-calendar'),
                Tables\Columns\TextColumn::make('keterangan'),
                Tables\Columns\TextColumn::make('hasil_interviewe1')
                    ->label('Interview 1'),
                Tables\Columns\TextColumn::make('hasil_interviewe2')
                    ->label('Interview 2'),
                Tables\Columns\TextColumn::make('hasil_interviewe3')
                    ->label('Interview 3'),
                Tables\Columns\TextColumn::make('sumary')
                    ->label('Summary'),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                    ->url(fn($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, shouldOpenInNewTab: true)

            ])
            ->filters([
                //
            ])
            ->headerActions([])
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

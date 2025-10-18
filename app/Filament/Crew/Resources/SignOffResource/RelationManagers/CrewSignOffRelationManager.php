<?php

namespace App\Filament\Crew\Resources\SignOffResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CrewSignOffRelationManager extends RelationManager
{
    protected static string $relationship = 'crewSignOff';
    protected static bool $isLazy = false;
    protected $listeners = ['refresh' => '$refresh'];
    protected static ?string $title = '';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->crewSignOff->count();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                 Section::make('Update Data Sign Off')
                    ->columnSpan(2)
                    ->columns(1)
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->required(),
                        Forms\Components\Textarea::make('keterangan'),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File Sign On')
                            ->columnSpan(1)
                            ->disk('public')
                            ->preserveFilenames()
                            ->directory('crew/signoff')
                            ->required(),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal'),
                Tables\Columns\TextColumn::make('keterangan'),
                Tables\Columns\TextColumn::make('tanggal'),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                    ->url(fn($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, shouldOpenInNewTab: true),
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

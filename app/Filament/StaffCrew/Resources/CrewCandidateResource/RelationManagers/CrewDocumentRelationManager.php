<?php

namespace App\Filament\StaffCrew\Resources\CrewCandidateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ContentTabPosition;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class CrewDocumentRelationManager extends RelationManager
{
    protected static string $relationship = 'crewDocument';
    protected static bool $isLazy = false;
    protected static ?string $title = 'Document';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->crewDocument->count();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kategory')
                    ->columns(1)
                    ->native(false)
                    ->columnSpan(1)
                    ->required()
                    ->options([
                        'Document Pelaut' => 'Document Pelaut',
                        'Sertifikat Keahlian / Pengukuhan' => 'Sertifikat Keahlian / Pengukuhan',
                    ]),
                Forms\Components\TextInput::make('nomor_document')
                    ->required()
                    ->columns(1),
                Forms\Components\Select::make('jenis_document')
                    ->columns(1)
                    ->native(false)
                    ->options([
                        'Passport' => 'Passport',
                        'Seaman Book' => 'Seaman Book',
                        'MCU' => 'MCU',
                        'COC' => 'COC',
                        'COE' => 'COE',
                        'GMDSS' => 'GMDSS'
                    ]),
                Forms\Components\TextInput::make('tempat_dikeluarkan')
                    ->required()
                    ->columns(1),
                Forms\Components\DatePicker::make('tanggal_dikeluarkan')
                    ->prefixIcon('heroicon-m-calendar')
                    ->required()
                    ->native(false)
                    ->columns(1),
                Forms\Components\DatePicker::make('tanggal_expired')
                    ->prefixIcon('heroicon-m-calendar')
                    ->required()
                    ->native(false)
                    ->columns(1),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->disk('public')
                    ->directory('crew/dokumen')
                    ->columnSpan(3)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get) {
                        try {
                            $nama_crew = optional($this->ownerRecord)->nama_crew ?? 'crew';
                            $jenis     = $get('jenis_document') ?? 'dokumen';
                            $now       = now()->format('YmdHis');

                            $filename = strtolower(
                                preg_replace('/[^A-Za-z0-9\-]/', '_', "{$nama_crew}-{$jenis}-{$now}")
                            ) . '.' . $file->getClientOriginalExtension();

                            \Log::info("FileUpload generate name: {$filename}");

                            return $filename;
                        } catch (\Throwable $e) {
                            \Log::error("FileUpload error: " . $e->getMessage());
                            throw $e; // biar error kelihatan
                        }
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_document')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->heading('')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_document')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_document')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategory')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tempat_dikeluarkan'),
                Tables\Columns\TextColumn::make('tanggal_dikeluarkan'),
                Tables\Columns\TextColumn::make('tanggal_expired'),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                    ->url(fn($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, shouldOpenInNewTab: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Document')
                    ->modalHeading('Add Data Document'),
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

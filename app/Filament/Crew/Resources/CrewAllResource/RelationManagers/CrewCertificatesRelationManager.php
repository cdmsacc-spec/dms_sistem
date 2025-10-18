<?php

namespace App\Filament\Crew\Resources\CrewAllResource\RelationManagers;

use App\Enums\StatusDocumentFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class CrewCertificatesRelationManager extends RelationManager
{
    protected static string $relationship = 'crewCertificates';
    protected static ?string $title = 'Certificates';
    protected static bool $isLazy = false;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->crewCertificates->count();
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kategori')
                    ->columns(1)
                    ->native(false)
                    ->required()
                    ->options([
                        'Keahlian STCW' => 'Keahlian STCW',
                        'Non-STCW' => 'Non-STCW'
                    ]),
                Forms\Components\TextInput::make('nomor_sertifikat')
                    ->required()
                    ->columns(1),
                Forms\Components\TextInput::make('nama_sertifikat')
                    ->required()
                    ->columns(1),
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
                    ->directory('crew/sertifikat')
                    ->columnSpan(3)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get) {
                        try {
                            // Ambil dari form field, bukan ownerRecord
                            $nama_crew = optional($this->ownerRecord)->nama_crew ?? 'crew';
                            $jenis     = $get('nama_sertifikat') ?? 'sertifikat';
                            $now       = now()->format('YmdHis');

                            // Bersihkan karakter yang tidak aman
                            $filename = strtolower(
                                preg_replace('/[^A-Za-z0-9\-]/', '_', "{$nama_crew}-{$jenis}-{$now}")
                            ) . '.' . $file->getClientOriginalExtension();

                            \Log::info("FileUpload generate name: {$filename}");

                            return $filename;
                        } catch (\Throwable $e) {
                            \Log::error("Error generate filename: " . $e->getMessage());
                            throw $e;
                        }
                    }),

            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_sertifikat')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->heading('')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_sertifikat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_sertifikat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tempat_dikeluarkan'),
                Tables\Columns\TextColumn::make('tanggal_dikeluarkan'),
                Tables\Columns\TextColumn::make('tanggal_expired'),
                Tables\Columns\TextColumn::make('status'),
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
                    ->label('Add Certificate')
                    ->modalHeading('Add Data Certificates')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status'] = StatusDocumentFile::UpToDate;
                        return $data;
                    }),
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

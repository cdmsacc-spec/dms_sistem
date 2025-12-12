<?php

namespace App\Filament\Crew\Resources\CrewAppraisals\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
                FileUpload::make('file')
                    ->label('File')
                    ->disk('public')
                    ->directory('crew/appraisal')
                    ->columnSpanFull()
                    ->required()
                    ->downloadable()
                    ->dehydrated(false)
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get, $record) {
                        try {
                            $nama_crew = optional($this->ownerRecord)->crew->nama_crew ?? 'crew';
                            $appraiser     = $get('aprraiser') ?? 'aprraiser';
                            $now       = now()->format('YmdHis');
                            $filename = strtolower(
                                preg_replace('/[^A-Za-z0-9\-]/', '_', "appraisal-crew-{$nama_crew}-form-appraiser-{$appraiser}-{$now}")
                            ) . '.' . $file->getClientOriginalExtension();

                            return $filename;
                        } catch (\Throwable $e) {
                            \Log::error("Error generate filename: " . $e->getMessage());
                            throw $e;
                        }
                    }),
                Textarea::make('keterangan')
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('aprraiser'),
                TextColumn::make('created_at')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d-M-Y'))
                    ->sortable()
                    ->label('Tanggal'),
                TextColumn::make('nilai')
                    ->sortable(),
                TextColumn::make('keterangan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('download')
                    ->size('sm')
                    ->color('info')
                    ->button()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('Preview')
                    ->label('Preview')
                    ->size('sm')
                    ->color('info')
                    ->button()
                    ->modalWidth('full')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => $record->jenis_dokumen)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file)))
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),
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

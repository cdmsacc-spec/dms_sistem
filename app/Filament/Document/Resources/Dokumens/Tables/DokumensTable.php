<?php

namespace App\Filament\Document\Resources\Dokumens\Tables;

use App\Models\Kapal;
use App\Models\Perusahaan;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class DokumensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultGroup('kapal.perusahaan.nama_perusahaan')
            ->groups([
                Group::make('kapal.perusahaan.nama_perusahaan')
                    ->label('Perusahaan')
                    ->collapsible(),
                Group::make('kapal.nama_kapal')
                    ->label('Kapal')
                    ->collapsible(),
                Group::make('jenisDokumen.nama_dokumen')
                    ->label('Jenis')
                    ->collapsible(),
            ])
            ->groupingDirectionSettingHidden()
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('kapal.perusahaan.nama_perusahaan')
                    ->searchable()
                    ->formatStateUsing(fn($state) => "<strong>{$state}</strong>")
                    ->html(),
                TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                TextColumn::make('jenisDokumen.nama_jenis')
                    ->label('Jenis')
                    ->searchable(),
                TextColumn::make('latestHistory.nomor_dokumen')
                    ->label('Nomor Dokumen'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'uptodate'    => 'success',
                        'near expiry' => 'warning',
                        default       => 'danger',
                    })->sortable(),
                TextColumn::make('jarak_hari')
                    ->label('Jarak hari expired')
                    ->sortable()
                    ->getStateUsing(
                        fn($record) => $record->jarak_hari !== null
                            ? $record->jarak_hari . ' hari'
                            : 'Tanpa Expired'
                    ),
                TextColumn::make('last_comment')->html()
            ])
            ->filters([
                Filter::make('kapal_perusahaan')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Select::make('perusahaan')
                            ->placeholder('')
                            ->label('Perusahaan')
                            ->options(Perusahaan::pluck('nama_perusahaan', 'id')->toArray())
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->columnSpan(1)
                            ->afterStateUpdated(function ($get, $set, $state) {

                                if (empty($state)) {
                                    $set('kapal', null);
                                }
                            }),
                        Select::make('kapal')
                            ->label('Kapal')
                            ->placeholder('')
                            ->columnSpan(1)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(function (callable $get) {
                                $perusahaanId = $get('perusahaan');
                                if ($perusahaanId) {
                                    return Kapal::where('id_perusahaan', $perusahaanId)
                                        ->pluck('nama_kapal', 'id')
                                        ->toArray();
                                }
                                return [];
                            })
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['perusahaan'])) {
                            $query->whereHas('kapal.perusahaan', fn($q) => $q->where('id', $data['perusahaan']));
                        }
                        if (!empty($data['kapal'])) {
                            $query->where('id_kapal', $data['kapal']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $texts = [];

                        if (!empty($data['perusahaan'])) {
                            $namaPerusahaan = Perusahaan::find($data['perusahaan'])->nama_perusahaan ?? '—';
                            $texts[] = "Perusahaan: {$namaPerusahaan}";
                        }

                        if (!empty($data['kapal'])) {
                            $namaKapal = Kapal::find($data['kapal'])->nama_kapal ?? '—';
                            $texts[] = "Kapal: {$namaKapal}";
                        }

                        return $texts ? implode(', ', $texts) : null;
                    }),

                SelectFilter::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->searchable()
                    ->native(false)
                    ->relationship('jenisDokumen', 'nama_jenis')
                    ->getOptionLabelFromRecordUsing(fn($record): string =>  $record->nama_jenis)
                    ->preload(),

                SelectFilter::make('status')
                    ->searchable()
                    ->label('Status Dokumen')
                    ->native(false)
                    ->options([
                        'uptodate' => 'UpToDate',
                        'near expiry' => 'Near Expiry',
                        'expired' => 'Expired'
                    ])
                    ->preload(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->filtersApplyAction(fn(Action $action) => $action
                ->button()
                ->badgeColor('danger')
                ->color('info')
                ->label('Terapkan Filter'),)
            ->recordActions([
                Action::make('download')
                    ->size('sm')
                    ->button()
                    ->color('info')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->latestHistory->file), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->latestHistory->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('Preview')
                    ->label('Preview')
                    ->size('sm')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('full')
                    ->button()
                    ->color('info')
                    ->modalHeading(fn($record) => $record->kapal->nama_kapal)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->latestHistory->file)))
                    ->visible(function ($record) {
                        $path = $record->latestHistory->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),
                ViewAction::make()->button(),
                EditAction::make()->button(),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('Download File')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->openUrlInNewTab()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $currentDate = now()->format('Y-m-d_H-i-s');
                            $zipFileName = "{$currentDate}" . "_documents.zip";

                            return new StreamedResponse(function () use ($records) {
                                $zip = new ZipStream();

                                foreach ($records as $record) {
                                    $filePath = optional($record->latestHistory)->file;
                                    if (! $filePath) continue;

                                    $disk = Storage::disk('public');
                                    $filePath = ltrim($filePath, '/');

                                    if ($disk->exists($filePath)) {
                                        $absolutePath = $disk->path($filePath);
                                        $safeName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $record->name ?? basename($filePath));
                                        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                                        $fileNameInZip = "{$safeName}.{$ext}";

                                        $zip->addFileFromPath($fileNameInZip, $absolutePath);
                                    }
                                }

                                $zip->finish();
                            }, 200, [
                                'Content-Type' => 'application/zip',
                                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
                            ]);
                        }),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Crew\Resources\AllCrews\RelationManagers;

use Carbon\Carbon;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Enums\ContentTabPosition;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExperienceRelationManager extends RelationManager
{
    protected static string $relationship = 'experience';
    public static function getBadge($ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->experience->count();
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('nama_kapal')
                    ->required()
                    ->columns(1),
                TextInput::make('tipe_kapal')
                    ->required()
                    ->columns(1),
                TextInput::make('nama_perusahaan')
                    ->required()
                    ->columns(1),
                TextInput::make('bendera')
                    ->required()
                    ->columns(1),
                TextInput::make('posisi')
                    ->required()
                    ->columns(1),
                TextInput::make('gt_kw')
                    ->label('GT / KW')
                    ->required()
                    ->columns(1),
                DatePicker::make('periode_awal')
                    ->prefixIcon('heroicon-m-calendar')
                    ->required()
                    ->native(false)
                    ->columns(1),
                DatePicker::make('periode_akhir')
                    ->prefixIcon('heroicon-m-calendar')
                    ->required()
                    ->native(false)
                    ->columns(1),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_kapal'),
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
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nama_perusahaan')
                    ->searchable(),
                TextColumn::make('nama_kapal')
                    ->searchable(),
                TextColumn::make('bendera')
                    ->searchable(),
                TextColumn::make('posisi')
                    ->searchable(),
                TextColumn::make('tipe_kapal')
                    ->searchable(),
                TextColumn::make('periode_awal'),
                TextColumn::make('periode_akhir'),
                TextColumn::make('masa_kerja')

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Experience')
                    ->modalHeading('Add Data Experience')
                    ->mutateDataUsing(function (array $data): array {
                        if (!empty($data['periode_awal']) && !empty($data['periode_akhir'])) {
                            $awal = Carbon::parse($data['periode_awal']);
                            $akhir = Carbon::parse($data['periode_akhir']);
                            $totalMonths = $awal->diffInMonths($akhir);
                            $years = intdiv($totalMonths, 12);
                            $months = $totalMonths % 12;
                            $masaKerja = '';
                            if ($years > 0) {
                                $masaKerja .= "{$years} tahun";
                            }
                            if ($months > 0) {
                                $masaKerja .= ($masaKerja ? ' ' : '') . "{$months} bulan";
                            }
                            if ($masaKerja === '') {
                                $masaKerja = 'Kurang dari 1 bulan';
                            }

                            $data['masa_kerja'] = $masaKerja;
                        } else {
                            $data['masa_kerja'] = 'Tidak diketahui';
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->mutateDataUsing(function (array $data): array {
                        if (!empty($data['periode_awal']) && !empty($data['periode_akhir'])) {
                            $awal = Carbon::parse($data['periode_awal']);
                            $akhir = Carbon::parse($data['periode_akhir']);
                            $totalMonths = $awal->diffInMonths($akhir);
                            $years = intdiv($totalMonths, 12);
                            $months = $totalMonths % 12;

                            $masaKerja = '';
                            if ($years > 0) {
                                $masaKerja .= "{$years} tahun";
                            }
                            if ($months > 0) {
                                $masaKerja .= ($masaKerja ? ' ' : '') . "{$months} bulan";
                            }
                            if ($masaKerja === '') {
                                $masaKerja = 'Kurang dari 1 bulan';
                            }
                            $data['masa_kerja'] = $masaKerja;
                        } else {
                            $data['masa_kerja'] = 'Tidak diketahui';
                        }

                        return $data;
                    }),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

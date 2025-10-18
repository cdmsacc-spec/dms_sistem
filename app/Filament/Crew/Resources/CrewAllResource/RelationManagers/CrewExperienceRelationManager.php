<?php

namespace App\Filament\Crew\Resources\CrewAllResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
                    ->modalHeading('Add Data Experience')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!empty($data['periode_awal']) && !empty($data['periode_akhir'])) {
                            $awal = Carbon::parse($data['periode_awal']);
                            $akhir = Carbon::parse($data['periode_akhir']);

                            // Ambil total bulan dulu
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!empty($data['periode_awal']) && !empty($data['periode_akhir'])) {
                            $awal = Carbon::parse($data['periode_awal']);
                            $akhir = Carbon::parse($data['periode_akhir']);

                            // Ambil total bulan dulu
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

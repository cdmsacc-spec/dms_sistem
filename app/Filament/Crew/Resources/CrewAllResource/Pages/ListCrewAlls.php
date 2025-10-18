<?php

namespace App\Filament\Crew\Resources\CrewAllResource\Pages;

use App\Enums\StatusCrew;
use App\Filament\Crew\Resources\CrewAllResource;
use App\Models\CrewApplicants;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;

class ListCrewAlls extends ListRecords
{
    protected static string $resource = CrewAllResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->uniqueField('email')
                ->closeModalByClickingAway(false)
                ->label('Import Data')
                ->color('warning')
                ->hidden(fn() => ! auth()->user()->can('create_crewapplicants'))
                ->handleBlankRows(true)
                ->extraModalFooterActions([
                    Action::make('Download Example File Template')
                        ->url(
                            fn() =>
                            asset('storage/templates/crew_sample.xlsx'),
                            shouldOpenInNewTab: true
                        ),
                ])
                ->fields([
                    ImportField::make('nama_crew')->required(),
                    ImportField::make('posisi_dilamar')->required(),
                    ImportField::make('tempat_lahir')->required(),
                    ImportField::make('tanggal_lahir')->mutateBeforeCreate(fn($value) => Carbon::parse($value)->format('Y-m-d'))->required(),
                    ImportField::make('jenis_kelamin')->required(),
                    ImportField::make('golongan_darah')->required(),
                    ImportField::make('status_identitas')->required(),
                    ImportField::make('agama')->required(),
                    ImportField::make('no_hp')->required(),
                    ImportField::make('no_telp_rumah')->required(),
                    ImportField::make('email')->required(),
                    ImportField::make('kebangsaan')->required(),
                    ImportField::make('suku')->required(),
                    ImportField::make('alamat_ktp')->required(),
                    ImportField::make('alamat_sekarang')->required(),
                    ImportField::make('status_rumah')->required(),
                    ImportField::make('tinggi_badan')->required(),
                    ImportField::make('berat_badan')->required(),
                    ImportField::make('ukuran_waerpack')->required(),
                    ImportField::make('ukuran_sepatu')->required(),
                    ImportField::make('nok_nama')->required(),
                    ImportField::make('nok_hubungan')->required(),
                    ImportField::make('nok_alamat')->required(),
                    ImportField::make('nok_hp')->required(),
                    Select::make('status_proses')
                        ->native(false)
                        ->required()
                        ->options([
                            'Draft' => 'Draft',
                            'Ready for Interview' => 'Ready for Interview'
                        ])
                ], columns: 2),

            Actions\CreateAction::make(),
        ];
    }
}

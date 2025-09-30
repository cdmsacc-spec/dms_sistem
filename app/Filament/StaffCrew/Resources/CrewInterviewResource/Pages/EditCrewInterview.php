<?php

namespace App\Filament\StaffCrew\Resources\CrewInterviewResource\Pages;

use App\Filament\StaffCrew\Resources\CrewInterviewResource;
use App\Filament\StaffCrew\Resources\CrewSignOnResource;
use Filament\Actions;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditCrewInterview extends EditRecord
{
    protected static string $resource = CrewInterviewResource::class;
    public function getTitle(): string
    {
        return 'Interview ' . $this->record->nama_crew;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Generate Form Interview')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->form([
                    TextInput::make('nama_kapal')
                        ->label('Nama Kapal')
                        ->required(),
                ])
                ->modalWidth(MaxWidth::Small)
                ->action(function (array $data, StaticAction  $action) {
                    $namaKapal = $data['nama_kapal'];
                    return  redirect()->route('generate.interview', [
                        'id' => $this->record->id,
                        'nama_kapal' => $namaKapal,
                    ]);
                }),
        ];
    }

    protected function afterSave(): void
    {
        $item = $this->form->getRawState();
        if ($item['tanggal'] != null && $item['file_path'] != null) {
            $file = is_array($item['file_path']) ? reset($item['file_path']) : $item['file_path'];
            $this->record->crewInterview()->create([
                'hasil_interviewe1' => $item['hasil_interviewe1'] ?? null,
                'hasil_interviewe2' => $item['hasil_interviewe2'] ?? null,
                'hasil_interviewe3' => $item['hasil_interviewe3'] ?? null,
                'sumary' => $item['sumary'] ?? null,
                'tanggal'    => \Carbon\Carbon::parse($item['tanggal'])->format('Y-m-d')  ?? null,
                'keterangan' => $item['keterangan'] ?? null,
                'file_path'  => $file
            ]);
            $this->form->fill();
            $this->dispatch('refresh');
            $this->redirect(CrewSignOnResource::getUrl('index'));
        }
    }
}

<?php

namespace App\Filament\StaffCrew\Resources\CrewSignOffResource\Pages;

use App\Filament\StaffCrew\Resources\CrewSignOffResource;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditCrewSignOff extends EditRecord
{
    protected static string $resource = CrewSignOffResource::class;
    public function getTitle(): string
    {
        return 'Sign Off ' . $this->record->nama_crew;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Generate Document Sign Off')
                ->label('Generate Document Sign Off')
                ->form([
                    Textarea::make('alasan_berhenti')
                        ->label('Alasan Berhenti')
                        ->required(),
                ])
                ->modalWidth(MaxWidth::Small)
                ->action(function (array $data) {
                    $alasan_berhenti = $data['alasan_berhenti'];
                    return redirect()->route('generate.signoff', [
                        'id' => $this->record->id,
                        'alasan_berhenti' => $alasan_berhenti,
                    ]);
                }),
        ];
    }

    protected function afterSave(): void
    {
        $item = $this->form->getRawState();
        if ($item['tanggal'] != null && $item['file_path'] != null) {
            $file = is_array($item['file_path']) ? reset($item['file_path']) : $item['file_path'];
            $this->record->crewSignOff()->create([
                'tanggal' => $item['tanggal'] ?? null,
                'keterangan' => $item['keterangan'] ?? null,
                'file_path'  => $file
            ]);
            $this->form->fill();
            $this->dispatch('refresh');
        }
    }
}

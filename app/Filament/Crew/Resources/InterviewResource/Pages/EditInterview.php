<?php

namespace App\Filament\Crew\Resources\InterviewResource\Pages;

use App\Enums\StatusCrew;
use App\Filament\Crew\Resources\InterviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\NamaKapal;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;

class EditInterview extends EditRecord
{
    protected static string $resource = InterviewResource::class;

    protected bool $isApproved = false;
    public ?string $tanggalInterview = null;

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('approved')
                ->label('Approved')
                ->color('primary')
                ->action(function (array $data) {
                    $this->isApproved = true;
                    $this->afterSave();
                }),
            Actions\Action::make('rejected')
                ->label('Rejected')
                ->color('danger')
                ->action(function (array $data) {
                    $this->isApproved = false;
                    $this->afterSave();
                }),
        ];
    }
    public function getTitle(): string
    {
        return 'Interview ' . $this->record->nama_crew;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Generate Form Interview')
                ->color('info')
                ->icon('heroicon-o-printer')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-printer')
                ->modalDescription('Pilih kapal untuk generate form interview')
                ->form([
                    DatePicker::make('tanggal')
                        ->label('Tanggal Interview')
                        ->prefixIcon('heroicon-m-calendar')
                        ->native(false)
                        ->required(),
                    Select::make('nama_kapal')
                        ->label('Pilih Kapal')
                        ->options(NamaKapal::pluck('nama_kapal', 'id'))
                        ->searchable()
                        ->native(false)
                        ->reactive()
                        ->required()
                        ->columnSpan(2),

                ])
                ->modalWidth(MaxWidth::Small)
                ->action(function (array $data, StaticAction  $action) {
                    $kapal = NamaKapal::find($data['nama_kapal']);
                    $namaKapal = $kapal?->nama_kapal;
                    $this->tanggalInterview = $data['tanggal'];
                    redirect()->route('generate.interview', [
                        'id' => $this->record->id,
                        'nama_kapal' => $namaKapal,
                        'tanggal_interview' => $this->tanggalInterview
                    ]);
                })
        ];
    }
    protected function afterSave(): void
    {
        $item = $this->form->getRawState();
        if (
            !empty($this->tanggalInterview) &&
            !empty($item['file_path']) &&
            !empty($item['sumary']) &&
            !empty($item['hasil_interviewe1']) &&
            !empty($item['hasil_interviewe2']) &&
            !empty($item['hasil_interviewe3'])
        ) {
            $file = is_array($item['file_path']) ? reset($item['file_path']) : $item['file_path'];
            $this->record->crewInterview()->create([
                'hasil_interviewe1' => $item['hasil_interviewe1'] ?? null,
                'hasil_interviewe2' => $item['hasil_interviewe2'] ?? null,
                'hasil_interviewe3' => $item['hasil_interviewe3'] ?? null,
                'sumary' => $item['sumary'] ?? null,
                'tanggal'    => \Carbon\Carbon::parse($this->tanggalInterview)->format('Y-m-d')  ?? null,
                'keterangan' => $item['keterangan'] ?? null,
                'file_path'  => $file
            ]);
            $this->form->fill();
            $this->dispatch('refresh');
            $this->record->update(['status_proses' => $this->isApproved ? StatusCrew::Standby->value : StatusCrew::Rejected->value]);
            Notification::make()
                ->title('Success')
                ->body('The crew interview results have been successfully saved, and the crew status has been set to ' . ($this->isApproved ?  StatusCrew::Standby->value : StatusCrew::Rejected->value) . '.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Incomplete Data!')
                ->body('Please make sure all required fields are filled in before saving.')
                ->danger()
                ->send();
        }
    }
}

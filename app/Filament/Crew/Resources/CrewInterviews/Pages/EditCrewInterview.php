<?php

namespace App\Filament\Crew\Resources\CrewInterviews\Pages;

use App\Filament\Crew\Resources\CrewInterviews\CrewInterviewResource;
use App\Models\Kapal;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EditCrewInterview extends EditRecord
{
    protected static string $resource = CrewInterviewResource::class;
    protected static ?string $slug = 'interview';

    protected bool $isApproved = false;

    protected function getFormActions(): array
    {
        return [
            Action::make('approved')
                ->label('Approved')
                ->color('info')
                ->action(function (array $data) {
                    $this->isApproved = true;
                    $this->afterSave();
                }),

            Action::make('rejected')
                ->label('Rejected')
                ->color('danger')
                ->action(function (array $data) {
                    $this->isApproved = false;
                    $this->afterSave();
                }),
        ];
    }
    public function getBreadcrumb(): string
    {
        return 'Interview ' . $this->record->nama_crew;
    }
    public function getTitle(): string|Htmlable
    {
        return 'Interview Crew';
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_file_interview')
                ->button()
                ->color('info')
                ->icon('heroicon-o-printer')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-printer')
                ->modalDescription('Pilih kapal untuk generate form interview')
                ->modalWidth(Width::Small)
                ->schema([
                    Select::make('nama_kapal')
                        ->label('Pilih Kapal')
                        ->placeholder('')
                        ->options(Kapal::pluck('nama_kapal', 'id'))
                        ->searchable()
                        ->native(false)
                        ->reactive()
                        ->required()
                        ->columnSpan(2),
                ])
                ->action(function (array $data,   $action) {
                    $kapal = Kapal::find($data['nama_kapal']);
                    $namaKapal = $kapal?->nama_kapal . ' Perusahaan ' . $kapal->perusahaan->nama_perusahaan;
                    redirect()->route('generate.interview', [
                        'id' => $this->record->id,
                        'nama_kapal' => $namaKapal,
                    ]);
                })
        ];
    }

    protected function afterSave(): void
    {

        DB::beginTransaction();
        $path = null;
        try {
            if (
                !empty($this->data['tanggal']) &&
                !empty($this->data['crewing']) &&
                !empty($this->data['user_operation']) &&
                !empty($this->data['summary']) &&
                !empty($this->data['keterangan']) &&
                !empty($this->data['file'])
            ) {

                $file = is_array($this->data['file']) ? reset($this->data['file']) : $this->data['file'];
                $path = $file->storeAs(
                    'crew/interview',
                    $file->getClientOriginalName(),
                    'public'
                );

                $this->record->interview()->create([
                    'tanggal' => Carbon::parse($this->data['tanggal'])->format('Y-m-d'),
                    'crewing' => $this->data['crewing'],
                    'user_operation' => $this->data['user_operation'],
                    'summary' => $this->data['summary'],
                    'keterangan' => $this->data['keterangan'],
                    'file' => $path
                ]);

                DB::commit();

                Notification::make()
                    ->title('Success')
                    ->body('The crew interview results have been successfully saved, and the crew status has been set to ' . ($this->isApproved ?  'standby' : 'rejected' . '.'))
                    ->success()
                    ->send();
                $this->form->fill();
                $this->dispatch('refresh');
                $this->record->update(['status' => $this->isApproved ? 'standby' : 'rejected']);
                redirect($this->getResource()::getUrl('index'));
            } else {
                Notification::make()
                    ->title('Failed')
                    ->body('Please make sure all required fields are filled in before saving.')
                    ->danger()
                    ->send();
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            Notification::make()
                ->title('Failed')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}

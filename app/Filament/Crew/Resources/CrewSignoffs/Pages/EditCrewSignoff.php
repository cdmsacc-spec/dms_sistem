<?php

namespace App\Filament\Crew\Resources\CrewSignoffs\Pages;

use App\Filament\Crew\Resources\CrewSignoffs\CrewSignoffResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\AlasanBerhenti;
use App\Models\Lookup;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditCrewSignoff extends EditRecord
{
    protected static string $resource = CrewSignoffResource::class;

    protected static ?string $slug = 'signoff';
    public ?string $tanggalSignOff = null;
    public function getBreadcrumb(): string
    {
        return 'Sign Off ' . $this->record->nama_crew;
    }
    public function getTitle(): string|Htmlable
    {
        return 'Sign Off';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('saved')
                ->label('Save Data')
                ->color('primary')
                ->action(function (array $data) {
                    $this->afterSave();
                }),
            $this->getCancelFormAction()
        ];
    }

    protected function afterSave(): void
    {
        $item = $this->form->getRawState();
        DB::transaction(function () use ($item) {
            $lookup = Lookup::where('code', 'sign_on')->lockForUpdate()->first();
            if (
                !empty($item['id_alasan']) &&
                !empty($item['tanggal']) &&
                !empty($item['keterangan'])
            ) {
                $signoff = $this->record->signoff()->create([
                    'nomor_dokumen' => $lookup->name ?? 1,
                    'id_alasan' =>  $item['id_alasan'],
                    'tanggal' =>  $item['tanggal'] ?? Carbon::now(),
                    'keterangan' => $item['keterangan'] ?? null,
                    'file'  => null
                ]);

                if ($signoff) {
                    $lookup->name = $lookup->name + 1;
                    $lookup->save();
                    $this->form->fill();
                    $this->dispatch('refresh');

                    Notification::make()
                        ->title('Success')
                        ->body('The crew signoff results have been successfully saved')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Failed')
                        ->danger()
                        ->body('An error occurred while saving the internship data.')
                        ->send();
                }
            } else {
                Notification::make()
                    ->title('Incomplete Data!')
                    ->body('Please make sure all required fields are filled in before saving.')
                    ->danger()
                    ->send();
            }
        });
    }
}

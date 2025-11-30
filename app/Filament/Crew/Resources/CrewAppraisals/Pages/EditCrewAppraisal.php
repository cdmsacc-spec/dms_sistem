<?php

namespace App\Filament\Crew\Resources\CrewAppraisals\Pages;

use App\Filament\Crew\Resources\CrewAppraisals\CrewAppraisalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditCrewAppraisal extends EditRecord
{
    protected static string $resource = CrewAppraisalResource::class;
    protected static ?string $slug = 'appraisal';

    public function getBreadcrumb(): string
    {
        return 'Appraisal ' . $this->record->crew->nama_crew;
    }
    public function getTitle(): string|Htmlable
    {
        return 'Appraisal';
    }
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Add Data')
                ->color('primary')
                ->icon('heroicon-o-check-circle'),
        ];
    }

    protected function afterSave()
    {
        DB::beginTransaction();
        $path = null;
        try {
            $item = $this->form->getRawState();
            if (
                !empty($item['aprraiser']) &&
                !empty($item['nilai']) &&
                !empty($item['keterangan']) &&
                !empty($item['file_appraisal'])
            ) {

                $file = is_array($item['file_appraisal']) ? reset($item['file_appraisal']) : $item['file_appraisal'];

                if (is_string($file)) {
                    $path = $file;
                } else {
                    // Baru simpan file upload
                    $path = $file->storeAs(
                        'crew/appraisal',
                        $file->getClientOriginalName(),
                        'public'
                    );
                }

                $this->record->appraisal()->create([
                    'id_kontrak' => $item['id'],
                    'aprraiser' => $item['aprraiser'],
                    'nilai' => $item['nilai'],
                    'keterangan' => $item['keterangan'],
                    'file' => $path,

                ]);
                DB::commit();

                Notification::make()
                    ->title('Success')
                    ->body('The crew appraisal results have been successfully saved')
                    ->success()
                    ->send();
                $this->form->fill();
                $this->dispatch('refresh');
            } else {
                Notification::make()
                    ->title('Incomplete Data!')
                    ->body('Please make sure all required fields are filled in before saving.')
                    ->danger()
                    ->send();
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            Notification::make()
                ->title('Failed')
                ->danger()
                ->body($th->getMessage())
                ->send();
        }
    }
}

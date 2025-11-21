<?php

namespace App\Filament\Document\Resources\Users\Pages;

use App\Filament\Document\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    protected static ?string $slug = 'edit-user-dokumen';

    public function getBreadcrumb(): string
    {
        return 'Edit user ' . $this->record->name;
    }
    public function getTitle(): string|Htmlable
    {
        return 'Edit User';
    }
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $password = $data['p'];
        if (!empty($password)) {
            $password = Hash::make($password);
            $data['password'] =  $password;
        } else {
            unset($data['p'], $data['p']);
        }
        unset($data['p']);
        return $data;
    }
}

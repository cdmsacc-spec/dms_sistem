<?php

namespace App\Filament\Crew\Resources\Users\Pages;

use App\Filament\Crew\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected static ?string $slug = 'add-user-crew';

    public function getBreadcrumb(): string
    {
        return 'Add user';
    }
    public function getTitle(): string|Htmlable
    {
        return 'Add User';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $password = $data['p'];
        if (!empty($password)) {
            $password = Hash::make($password);
            $data['password'] =  $password;
        } else {
            $data['password'] =  "12345";
        }
        unset($data['p']);
        return $data;
    }

    public function afterCreate(): void
    {
        $user = $this->record;
        $user->assignRole('staff_dokumen');
    }
}

<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
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

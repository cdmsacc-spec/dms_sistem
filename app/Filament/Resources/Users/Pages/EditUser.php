<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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

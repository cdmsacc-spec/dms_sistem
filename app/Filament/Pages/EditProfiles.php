<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EditProfiles extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasPageShield;
    protected  string $view = 'filament.pages.edit-profiles';

    public ?string $name = '';
    public ?string $email = '';
    public ?string $password = '';

    public ?string $c = '';

    public string|array|null $avatar = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->form->fill($user->only(['name', 'email', 'avatar', 'password']));
    }

    protected function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Personal Info')
                ->aside()
                ->schema([
                    FileUpload::make('avatar')
                        ->disk('public')
                        ->directory('user/profile')
                        ->avatar()
                        ->image()
                        ->imageEditor()
                        ->circleCropper()
                        ->inlineLabel(),
                    TextInput::make('name')
                        ->required()
                        ->inlineLabel(),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->inlineLabel(),
                ]),

            Section::make('Password')
                ->aside()
                ->footer([
                    Action::make('save')
                        ->button()
                        ->color('info')
                        ->action('save')

                ])
                ->schema([
                    TextInput::make('c')
                        ->password()
                        ->label('Password Baru')
                        ->nullable()
                        ->revealable(),
                ])
        ]);
    }


    public function save()
    {

        $data = $this->form->getState();
        $user = auth()->user();

        if (!empty($data['c'])) {
            $data['c'] = Hash::make($data['c']);
            $data['password'] =  $data['c'];
        } else {
            unset($data['c'], $data['c']);
        }

        $user->update($data);
        Notification::make()
            ->title('Saved successfully')
            ->body('Profile berhasil diperbarui')
            ->success()
            ->send();
    }

    protected function getFormModel(): mixed
    {
        return auth()->user();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static bool $shouldRegisterTenancyHooks = false;
}

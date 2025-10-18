<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Pages\Auth\Login;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class LoginStaffDocument extends Login
{
    public function authenticate(): LoginResponse
    {
        // Panggil parent untuk melakukan autentikasi
        $response = parent::authenticate();

        // Jika login berhasil, user sudah ter-authenticate
        $user = Auth::user();

        activity('auth')
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('login')
            ->log('User logged in');

        return $response; 
    }


    public function getHeading(): string|Htmlable
    {
        return __('Staff Document Login');
    }
    public function getTitle(): string | Htmlable
    {
        return 'Login';
    }
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('email anda tidak valid'),
            'data.password' => __('password anda tidak valid'),
        ]);
    }
    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->color('primary')
            ->label('Login')
            ->submit('authenticate');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->suffixIcon('heroicon-m-globe-alt')
            ->suffixIconColor('primary')
            ->extraInputAttributes(['tabindex' => 1]);
    }
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('Password'))
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete(autocomplete: 'current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}

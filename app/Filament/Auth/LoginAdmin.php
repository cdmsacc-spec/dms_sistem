<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as PagesLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;


class LoginAdmin extends PagesLogin
{
    public function getHeading(): string|Htmlable
    {
        return __('Admin Login');
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

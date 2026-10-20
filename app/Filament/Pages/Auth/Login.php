<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected static string $layout = 'filament.auth.layout';

    protected static string $view = 'filament.auth.login';

    public function boot(): void
    {
        app()->setLocale('es');
    }

    public function authenticate(): ?LoginResponse
    {
        return parent::authenticate();
    }

    public function getHeading(): string
    {
        return 'Iniciar sesión';
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Correo electrónico')
            ->placeholder('tucorreo@empresa.com');
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label('Contraseña')
            ->placeholder('••••••••');
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label('Mantener sesión iniciada');
    }
}

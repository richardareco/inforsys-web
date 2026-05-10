<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        // Forzar "recordarme" siempre activo
        $this->form->fill([
            ...$this->form->getState(),
            'remember' => true,
        ]);

        return parent::authenticate();
    }
}

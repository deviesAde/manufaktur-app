<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class CustomLogin extends BaseLogin
{
    public function getTitle(): string|Htmlable
    {
        return 'Login - PT Raung Global Mandiri';
    }

    public function getHeading(): string|Htmlable
    {
        return new HtmlString('
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Selamat Datang Kembali
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Sistem Inventory PT Raung Global Mandiri
                </p>
            </div>
        ');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null; // Menghilangkan subheading default
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent()
                    ->prefixIcon('heroicon-m-envelope')
                    ->prefixIconColor('primary'),

                $this->getPasswordFormComponent()
                    ->prefixIcon('heroicon-m-lock-closed')
                    ->prefixIconColor('primary'),

                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->placeholder('nama@perusahaan.com')
            ->extraInputAttributes([
                'class' => 'transition duration-200',
            ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->revealable()
            ->required()
            ->placeholder('Masukkan password Anda')
            ->extraInputAttributes([
                'class' => 'transition duration-200',
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    // Mengubah alignment form menjadi center (opsional)
    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}

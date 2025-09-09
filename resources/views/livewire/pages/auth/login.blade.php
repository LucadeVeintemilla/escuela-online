<?php

use App\Livewire\Forms\LoginForm;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: RouteServiceProvider::HOME, navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form wire:submit="login">
        <h1 class="h5 text-center mb-3">Iniciar sesión</h1>
        <!-- Email  -->
        <div class="form-group mb-3">
            <x-input-label for="email" :value="__('Email')" class="form-label" />
            <x-text-input wire:model="form.email" id="email" class="form-control" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="text-danger small mt-1" />
        </div>

      
        <div class="form-group mb-3">
            <x-input-label for="password" :value="__('Contraseña')" class="form-label" />

            <x-text-input wire:model="form.password" id="password" class="form-control"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="text-danger small mt-1" />
        </div>

        <div class="form-check mb-3">
            <input wire:model="form.remember" id="remember" type="checkbox" class="form-check-input" name="remember">
            <label for="remember" class="form-check-label">{{ __('Recordarme') }}</label>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a class="small" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif

            <div class="d-flex gap-2">
                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm" wire:navigate>{{ __('Crear cuenta') }}</a>
                <x-primary-button class="btn btn-primary">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </div>
    </form>
</div>

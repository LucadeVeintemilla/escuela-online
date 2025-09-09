<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Directly reset password without email link.
     */
    public function resetPassword(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            $this->addError('email', __('No se encontró un usuario con ese correo.'));
            return;
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Limpia campos y muestra mensaje
        $this->reset(['email','password','password_confirmation']);
        session()->flash('status', __('Tu contraseña fue restablecida correctamente. Ahora puedes iniciar sesión.'));
    }
}; ?>

<div>
    <div class="mb-3 text-muted small">
        {{ __('Restablece tu contraseña ingresando tu correo y la nueva contraseña. No enviaremos ningún email.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form wire:submit="resetPassword">
        <!-- Email -->
        <div class="form-group mb-3">
            <x-input-label for="email" :value="__('Email')" class="form-label" />
            <x-text-input wire:model="email" id="email" class="form-control" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="text-danger small mt-1" />
        </div>

        <!-- New Password -->
        <div class="form-group mb-3">
            <x-input-label for="password" :value="__('Nueva contraseña')" class="form-label" />
            <x-text-input wire:model="password" id="password" class="form-control" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="text-danger small mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group mb-3">
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="form-label" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="form-control" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="text-danger small mt-1" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button class="btn btn-primary">
                {{ __('Restablecer contraseña') }}
            </x-primary-button>
        </div>
    </form>
</div>

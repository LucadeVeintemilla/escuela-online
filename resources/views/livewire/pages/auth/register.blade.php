<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Genero;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Campos adicionales requeridos por Docentes/Alumnos
    public string $nombre_1 = '';
    public string $apellido_1 = '';
    public string $apellido_2 = '';
    public string $dni = '';
    public int|string $genero_id = '';

    // Selector de rol (Alumno/Docente)
    public string $rol = 'Alumno';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'rol' => ['required', 'in:Alumno,Docente'],
            'nombre_1' => ['required','string','max:255'],
            'apellido_1' => ['required','string','max:255'],
            'apellido_2' => ['required','string','max:255'],
            'dni' => ['required','string','size:8','unique:docentes,dni', 'unique:alumnos,dni'],
            'genero_id' => ['required','exists:generos,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Asignar role_id según selección
        $validated['role_id'] = Rol::where('rol', $validated['rol'])->value('id');

        event(new Registered($user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => $validated['role_id'],
        ])));

        // Crear registro relacionado en docentes o alumnos
        if ($validated['rol'] === 'Docente') {
            Docente::create([
                'user_id' => $user->id,
                'nombre_1' => $validated['nombre_1'],
                'nombre_2' => null,
                'apellido_1' => $validated['apellido_1'],
                'apellido_2' => $validated['apellido_2'],
                'dni' => $validated['dni'],
                'genero_id' => (int)$validated['genero_id'],
                'aula_id' => null, // se puede asignar luego
                'activo' => false,
                'correo' => $user->email,
                'celular' => null,
                'observacion' => null,
            ]);
        } else { // Alumno
            Alumno::create([
                'user_id' => $user->id,
                'nombre_1' => $validated['nombre_1'],
                'nombre_2' => null,
                'apellido_1' => $validated['apellido_1'],
                'apellido_2' => $validated['apellido_2'],
                'dni' => $validated['dni'],
                'genero_id' => (int)$validated['genero_id'],
                'observacion' => null,
            ]);
        }

        Auth::login($user);

        $this->redirect(RouteServiceProvider::HOME, navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <h1 class="h5 text-center mb-3">Crear cuenta</h1>

        <!-- Rol -->
        <div class="form-group mb-3">
            <x-input-label for="rol" :value="__('Rol')" class="form-label" />
            <select id="rol" name="rol" wire:model="rol" class="form-control">
                <option value="Alumno">Alumno</option>
                <option value="Docente">Docente</option>
            </select>
            <x-input-error :messages="$errors->get('rol')" class="text-danger small mt-1" />
        </div>
        <!-- Nombre completo (para la cuenta) -->
        <div class="form-group mb-3">
            <x-input-label for="name" :value="__('Nombre completo')" class="form-label" />
            <x-text-input wire:model="name" id="name" class="form-control" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="text-danger small mt-1" />
        </div>

        <!-- Datos personales requeridos para el perfil -->
        <div class="form-row">
            <div class="form-group mb-3">
                <x-input-label for="nombre_1" :value="__('Primer nombre')" class="form-label" />
                <x-text-input wire:model="nombre_1" id="nombre_1" class="form-control" type="text" name="nombre_1" required />
                <x-input-error :messages="$errors->get('nombre_1')" class="text-danger small mt-1" />
            </div>
            <div class="form-group mb-3">
                <x-input-label for="apellido_1" :value="__('Primer apellido')" class="form-label" />
                <x-text-input wire:model="apellido_1" id="apellido_1" class="form-control" type="text" name="apellido_1" required />
                <x-input-error :messages="$errors->get('apellido_1')" class="text-danger small mt-1" />
            </div>
            <div class="form-group mb-3">
                <x-input-label for="apellido_2" :value="__('Segundo apellido')" class="form-label" />
                <x-text-input wire:model="apellido_2" id="apellido_2" class="form-control" type="text" name="apellido_2" required />
                <x-input-error :messages="$errors->get('apellido_2')" class="text-danger small mt-1" />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group mb-3">
                <x-input-label for="dni" :value="__('DNI')" class="form-label" />
                <x-text-input wire:model="dni" id="dni" class="form-control" type="text" name="dni" required />
                <x-input-error :messages="$errors->get('dni')" class="text-danger small mt-1" />
            </div>
            <div class="form-group mb-3">
                <x-input-label for="genero_id" :value="__('Género')" class="form-label" />
                <select id="genero_id" name="genero_id" wire:model="genero_id" class="form-control">
                    <option value="">Seleccionar...</option>
                    @foreach(\App\Models\Genero::all() as $g)
                        <option value="{{ $g->id }}">{{ $g->genero }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('genero_id')" class="text-danger small mt-1" />
            </div>
        </div>

        <!-- Email Address -->
        <div class="form-group mb-3">
            <x-input-label for="email" :value="__('Email')" class="form-label" />
            <x-text-input wire:model="email" id="email" class="form-control" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="text-danger small mt-1" />
        </div>

        <!-- Password -->
        <div class="form-group mb-3">
            <x-input-label for="password" :value="__('Contraseña')" class="form-label" />

            <x-text-input wire:model="password" id="password" class="form-control"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="text-danger small mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group mb-3">
            <x-input-label for="password_confirmation" :value="__('Confirma tu contraseña')" class="form-label" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="form-control"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="text-danger small mt-1" />
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a class="small" href="{{ route('login') }}" wire:navigate>
                {{ __('Ya tienes una cuenta?') }}
            </a>

            <x-primary-button class="btn btn-primary">
                {{ __('Registrar') }}
            </x-primary-button>
        </div>
    </form>
</div>

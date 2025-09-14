<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to login to use the customized AdminLTE guest layout
Route::redirect('/', '/login')->name('home');

// Unified dashboard redirect based on role
Route::get('dashboard', function () {
    $user = Auth::user();
    if (!$user) {
        return redirect()->route('login');
    }
    $role = optional($user->role)->rol;
    if ($role === 'Docente') {
        return redirect()->route('docente.dashboard');
    }
    if ($role === 'Alumno') {
        return redirect()->route('alumno.actividades');
    }
    // Default to Admin main panel
    return redirect()->route('app');
})->middleware(['auth'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Logout route (POST)
Route::post('logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware(['auth'])->name('logout');

// Docente dashboard (solo usuarios con rol Docente)
Route::middleware(['auth', 'role:Docente'])->group(function () {
    Route::redirect('docente', 'docente/inicio')->name('docente.dashboard');
    Route::view('docente/inicio', 'livewire.docente.inicio-page')->name('docente.inicio');
    Route::view('docente/calificar', 'livewire.docente.calificar-page')->name('docente.calificar');
    Route::view('docente/cursos', 'livewire.docente.cursos-page')->name('docente.cursos');
    Route::view('docente/estudiantes', 'livewire.docente.estudiantes-page')->name('docente.estudiantes');
    Route::view('docente/reportes', 'livewire.docente.reportes-page')->name('docente.reportes');
    Route::view('docente/actividades', 'livewire.docente.actividades-page')->name('docente.actividades');
    Route::view('docente/actividad/calificar', 'livewire.docente.calificar-actividad-page')->name('docente.actividad.calificar');
});

// Admin main panel (legacy forms) at /app
Route::get('app', function () {
    $user = Auth::user();
    if (!$user) { return redirect()->route('login'); }
    $role = optional($user->role)->rol;
    if ($role === 'Admin') {
        return view('components.layouts.app');
    }
    // Para otros roles, redirigir a su dashboard unificado
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('app');

// Admin: asignaciones de docente (aula y materias)
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('admin/asignaciones', \App\Livewire\AdminAsignaciones::class)->name('admin.asignaciones');
});

// Alumno (perfil estudiante)
Route::middleware(['auth', 'role:Alumno'])->group(function () {
    Route::view('alumno/inicio', 'livewire.alumno.inicio-page')->name('alumno.inicio');
    Route::view('alumno/actividades', 'livewire.alumno.actividades-page')->name('alumno.actividades');
    Route::view('alumno/actividad', 'livewire.alumno.actividad-page')->name('alumno.actividad');
    Route::view('alumno/mi-aula', 'livewire.alumno.mi-aula-page')->name('alumno.mi-aula');
});

// Admin: anuncios gestión
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::view('admin/anuncios', 'livewire.admin-anuncios-page')->name('admin.anuncios');
});

require __DIR__.'/auth.php';

// Fallback to serve files from public storage if the symlink is not working (useful on Windows/OneDrive)
Route::get('storage/{path}', function (string $path) {
    $clean = ltrim($path, '/');
    $full = storage_path('app/public/'.str_replace(['..', '\\'], ['', '/'], $clean));
    if (file_exists($full)) {
        return response()->file($full);
    }
    abort(404);
})->where('path', '.*');

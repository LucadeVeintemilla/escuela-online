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

Route::view('/', 'welcome');

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
    // Default to Admin main panel
    return redirect()->route('app');
})->middleware(['auth', 'verified'])->name('dashboard');

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
Route::middleware(['auth', 'verified', 'role:Docente'])->group(function () {
    Route::redirect('docente', 'docente/cursos')->name('docente.dashboard');
    Route::view('docente/calificar', 'livewire.docente.calificar-page')->name('docente.calificar');
    Route::view('docente/cursos', 'livewire.docente.cursos-page')->name('docente.cursos');
    Route::view('docente/actividades', 'livewire.docente.actividades-page')->name('docente.actividades');
    Route::view('docente/actividad/calificar', 'livewire.docente.calificar-actividad-page')->name('docente.actividad.calificar');
});

// Admin main panel (legacy forms) at /app
Route::view('app', 'components.layouts.app')
    ->middleware(['auth', 'verified', 'role:Admin'])
    ->name('app');

// Admin: asignaciones de docente (aula y materias)
Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::get('admin/asignaciones', \App\Livewire\AdminAsignaciones::class)->name('admin.asignaciones');
});

require __DIR__.'/auth.php';

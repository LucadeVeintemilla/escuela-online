<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DocenteAnuncios extends Component
{
    public string $anuncio = '';
    public string $descripcion = '';
    public ?string $inicio = null; // Y-m-d H:i
    public ?string $fin = null;    // Y-m-d H:i
    public bool $activo = true;
    public ?string $observacion = null;

    public string $mensaje = '';

    protected function rules(): array
    {
        return [
            'anuncio' => ['required','string','max:255'],
            'descripcion' => ['required','string','max:255'],
            'inicio' => ['nullable','date'],
            'fin' => ['nullable','date','after_or_equal:inicio'],
            'activo' => ['boolean'],
            'observacion' => ['nullable','string','max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'inicio.date' => 'La fecha de inicio no es válida.',
            'fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ];
    }

    public function guardar(): void
    {
        $this->validate();
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }

        DB::beginTransaction();
        try {
            // Crear anuncio
            $anuncioId = DB::table('anuncios')->insertGetId([
                'anuncio' => $this->anuncio,
                'descripcion' => $this->descripcion,
                'usuario_id' => $user->id,
                'inicio' => $this->inicio ? date('Y-m-d H:i:s', strtotime($this->inicio)) : null,
                'fin' => $this->fin ? date('Y-m-d H:i:s', strtotime($this->fin)) : null,
                'activo' => $this->activo ? 1 : 0,
                'observacion' => $this->observacion,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Destinatario fijo: alumnos
            DB::table('anuncio_alumno_generals')->insert([
                'anuncio_id' => $anuncioId,
                'observacion' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            $this->mensaje = 'Anuncio para alumnos creado correctamente.';
            $this->reset(['anuncio','descripcion','inicio','fin','observacion']);
            $this->activo = true;

            $this->dispatch('docente-anuncios:creado', id: $anuncioId);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensaje = 'Error al crear anuncio: '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.components.docente-anuncios');
    }
}

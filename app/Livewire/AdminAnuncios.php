<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AdminAnuncios extends Component
{
    public string $anuncio = '';
    public string $descripcion = '';
    public string $destinatario = 'todos'; // 'docente' | 'alumno' | 'todos'
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
            'destinatario' => ['required', Rule::in(['docente','alumno','todos'])],
            'inicio' => ['required','date'],
            'fin' => ['required','date','after_or_equal:inicio'],
            'activo' => ['boolean'],
            'observacion' => ['nullable','string','max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'inicio.required' => 'La fecha de inicio es obligatoria.',
            'fin.required' => 'La fecha de fin es obligatoria.',
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
            // Insertar en anuncios
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

            // Enlazar según destinatario
            if ($this->destinatario === 'docente') {
                DB::table('anuncio_docente_generals')->insert([
                    'anuncio_id' => $anuncioId,
                    'observacion' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($this->destinatario === 'alumno') {
                DB::table('anuncio_alumno_generals')->insert([
                    'anuncio_id' => $anuncioId,
                    'observacion' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else { // todos
                DB::table('anuncio_generals')->insert([
                    'anuncio_id' => $anuncioId,
                    'observacion' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            $this->mensaje = 'Anuncio creado correctamente.';
            $this->reset(['anuncio','descripcion','inicio','fin','observacion']);
            $this->destinatario = 'todos';
            $this->activo = true;

            $this->dispatch('admin-anuncios:creado', id: $anuncioId);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensaje = 'Error al crear anuncio: '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.components.admin-anuncios');
    }
}

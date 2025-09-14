<?php

namespace App\Livewire;

use App\Models\Alumno;
use App\Models\Actividad;
use App\Models\Contenido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class AlumnoActividadRecursos extends Component
{
    use WithFileUploads;

    public ?Alumno $alumno = null;
    public ?Actividad $actividad = null;

    public array $recursos = [];
    public string $mensaje = '';
    public bool $disponible = true;
    public int $intentosUsados = 0;
    public ?int $intentosMax = null; // null o 0 = ilimitado

    // Upload inputs
    public $archivo; // Livewire temporary file
    public ?string $titulo = null;
    // Edición de recursos del alumno
    public ?int $editRecursoId = null;
    public ?string $editRecursoTitulo = null;
    public $replaceArchivo = null; // Livewire temporary file para reemplazo

    protected function rules(): array
    {
        return [
            'archivo' => 'required|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp3,mp4,avi,mov,mpeg,ogg,webm,zip,rar,7z,jpg,jpeg,png',
            'titulo' => 'nullable|string|max:255',
        ];
    }

    public function mount(): void
    {
        Log::info('AlumnoActividadRecursos.mount start');
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }
        // Vincular solo por user_id (no usamos columna 'correo')
        $this->alumno = Alumno::where('user_id', $user->id)->first();
        if (!$this->alumno) { $this->mensaje = 'Tu usuario no está vinculado a un Alumno.'; return; }

        $actividadId = request()->integer('actividad_id');
        $this->actividad = $actividadId ? Actividad::find($actividadId) : null;
        if (!$this->actividad) { $this->mensaje = 'Actividad no encontrada.'; Log::warning('Actividad no encontrada', ['actividad_id' => $actividadId]); return; }
        if ($this->actividad->aula_id !== $this->alumno->aula_id) { $this->mensaje = 'No tienes acceso a esta actividad.'; Log::warning('Aula mismatch', ['actividad_aula' => $this->actividad->aula_id, 'alumno_aula' => $this->alumno->aula_id]); return; }

        $this->cargarRecursos();
        $this->actualizarDisponibilidad();
        $this->cargarIntentos();
        $this->dispatch('debug', ['where' => 'mount', 'actividad_id' => $this->actividad->id, 'alumno_id' => $this->alumno->id]);
    }

    protected function actualizarDisponibilidad(): void
    {
        if (!$this->actividad) { $this->disponible = false; return; }
        $now = now();
        $this->disponible = !(
            ($this->actividad->inicio && $now->lt($this->actividad->inicio)) ||
            ($this->actividad->fin && $now->gt($this->actividad->fin))
        );
        if (!$this->disponible && empty($this->mensaje)) {
            $this->mensaje = 'La actividad no está disponible para subir recursos en este momento.';
        }
    }

    protected function cargarIntentos(): void
    {
        if (!$this->actividad || !$this->alumno) { $this->intentosUsados = 0; $this->intentosMax = null; return; }
        $this->intentosMax = $this->actividad->max_intentos ?: null; // 0/null => ilimitado
        // Contar recursos subidos por este alumno para esta actividad
        $this->intentosUsados = \App\Models\Contenido::where('actividad_id', $this->actividad->id)
            ->where('alumno_id', $this->alumno->id)
            ->count();
        // Si hay límite y ya alcanzó o superó, bloquear
        if ($this->intentosMax !== null && $this->intentosUsados >= $this->intentosMax) {
            $this->disponible = false;
            if (empty($this->mensaje)) {
                $this->mensaje = 'Has alcanzado el número máximo de intentos permitidos para esta actividad.';
            }
        }
    }

    protected function cargarRecursos(): void
    {
        $this->recursos = Contenido::where('actividad_id', $this->actividad->id)
            ->orderByDesc('id')
            ->get(['id','contenido','usuario_id','alumno_id','path','created_at'])
            ->map(function($c){
                return [
                    'id' => $c->id,
                    'titulo' => $c->contenido,
                    'propietario' => $c->alumno_id ? 'Alumno' : 'Docente/Directivo',
                    'url' => $c->path ? Storage::disk('public')->url($c->path) : null,
                    'fecha' => $c->created_at,
                ];
            })->toArray();
    }

    public function subir(): void
    {
        Log::info('AlumnoActividadRecursos.subir called');
        $this->dispatch('debug', ['where' => 'subir:called']);
        $this->validate();
        if (!$this->actividad || !$this->alumno) { Log::warning('Missing actividad or alumno'); return; }
        $this->actualizarDisponibilidad();
        $this->cargarIntentos();
        if (!$this->disponible) {
            $now = now();
            $this->dispatch('debug', ['where' => 'subir:blocked_by_window', 'inicio' => (string)$this->actividad->inicio, 'fin' => (string)$this->actividad->fin, 'now' => (string)$now]);
            Log::info('Upload blocked by availability window', ['inicio' => (string)$this->actividad->inicio, 'fin' => (string)$this->actividad->fin, 'now' => (string)$now]);
            return;
        }
        // Check attempts
        if ($this->intentosMax !== null && $this->intentosUsados >= $this->intentosMax) {
            $this->mensaje = 'Has alcanzado el número máximo de intentos.';
            $this->dispatch('debug', ['where' => 'subir:blocked_by_attempts', 'usados' => $this->intentosUsados, 'max' => $this->intentosMax]);
            return;
        }

        $original = $this->archivo->getClientOriginalName();
        $name = uniqid('rec_')."_".$original;
        $path = $this->archivo->storeAs('actividad_recursos/'.$this->actividad->id, $name, 'public');
        $this->dispatch('debug', ['where' => 'subir:stored_file', 'path' => $path]);

        // Asegurar que 'contenido' sea único para evitar colisión con índice unique
        $tituloBase = $this->titulo ?: $original;
        $tituloUnico = $tituloBase.' ['.now()->format('Y-m-d H:i:s').']';

        try {
            // Determinar tipo_contenido_id según la extensión (coincide con TipoContenidoSeeder)
            $ext = strtolower($this->archivo->getClientOriginalExtension());
            $tipoContenidoId = match (true) {
                in_array($ext, ['doc','docx','pdf','txt','odt','rtf']) => 1, // Documento de texto
                in_array($ext, ['ppt','pptx','odp','pps','ppsx']) => 2,      // Presentación digital
                in_array($ext, ['mp3','wav','ogg','wma','m4a','midi','opus']) => 3, // Sonido
                in_array($ext, ['jpeg','jpg','tiff','bmp','png','gif','svg','ico']) => 4, // Imagen
                in_array($ext, ['avi','mp4','mkv','mpeg','webm']) => 5,      // Video
                in_array($ext, ['exe','msi','dmg','pkg','apk']) => 6,        // Ejecutable/instalador
                default => 7,                                                // Otro
            };
            $recurso = new Contenido();
            $recurso->contenido = $tituloUnico;
            $recurso->tipo_contenido_id = $tipoContenidoId;
            $recurso->observacion = null;
            $recurso->usuario_id = Auth::id();
            $recurso->actividad_id = $this->actividad->id;
            $recurso->alumno_id = $this->alumno->id;
            $recurso->path = $path;
            $recurso->save();
            Log::info('Contenido guardado', ['contenido_id' => $recurso->id, 'alumno_id' => $this->alumno->id, 'actividad_id' => $this->actividad->id]);
            $this->dispatch('debug', ['where' => 'subir:saved_db', 'contenido_id' => $recurso->id]);
        } catch (\Throwable $e) {
            // Si falla el guardado, revertir archivo y reportar
            if ($path && \Storage::disk('public')->exists($path)) {
                \Storage::disk('public')->delete($path);
            }
            $this->mensaje = 'No se pudo guardar el recurso: '.$e->getMessage();
            Log::error('Error guardando contenido', ['error' => $e->getMessage()]);
            $this->dispatch('debug', ['where' => 'subir:error_db', 'error' => $e->getMessage()]);
            return;
        }

        // Reset input
        $this->reset(['archivo','titulo']);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Recurso subido.']);
        $this->cargarRecursos();
        $this->cargarIntentos();
    }

    // ===== CRUD del alumno sobre sus propios recursos =====
    public function iniciarEditarRecurso(int $recursoId): void
    {
        if (!$this->alumno) return;
        $recurso = Contenido::where('id', $recursoId)
            ->where('alumno_id', $this->alumno->id)->first();
        if (!$recurso) { $this->mensaje = 'No puedes editar este recurso.'; return; }
        $this->editRecursoId = $recurso->id;
        $this->editRecursoTitulo = $recurso->contenido;
        $this->replaceArchivo = null;
    }

    public function guardarRecursoEditado(): void
    {
        if (!$this->alumno || !$this->editRecursoId) return;
        // Validaciones: título requerido; archivo reemplazo opcional con reglas
        $this->validateOnly('editRecursoTitulo', ['editRecursoTitulo' => 'required|string|max:255']);
        if ($this->replaceArchivo) {
            $this->validateOnly('replaceArchivo', ['replaceArchivo' => 'file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp3,mp4,avi,mov,mpeg,ogg,webm,zip,rar,7z,jpg,jpeg,png']);
        }

        $recurso = Contenido::where('id', $this->editRecursoId)
            ->where('alumno_id', $this->alumno->id)->first();
        if (!$recurso) { $this->mensaje = 'No puedes editar este recurso.'; return; }

        // Actualizar título
        $recurso->contenido = $this->editRecursoTitulo;

        // Reemplazar archivo si se proporcionó
        if ($this->replaceArchivo) {
            if ($recurso->path && \Storage::disk('public')->exists($recurso->path)) {
                \Storage::disk('public')->delete($recurso->path);
            }
            $original = $this->replaceArchivo->getClientOriginalName();
            $name = uniqid('rec_')."_".$original;
            $path = $this->replaceArchivo->storeAs('actividad_recursos/'.$this->actividad->id, $name, 'public');
            $recurso->path = $path;
        }

        $recurso->save();
        $this->mensaje = 'Recurso actualizado.';
        $this->editRecursoId = null;
        $this->editRecursoTitulo = null;
        $this->replaceArchivo = null;
        $this->cargarRecursos();
        $this->cargarIntentos();
    }


    public function eliminarRecurso(int $recursoId): void
    {
        if (!$this->alumno) return;
        $recurso = Contenido::where('id', $recursoId)
            ->where('alumno_id', $this->alumno->id)->first();
        if (!$recurso) { $this->mensaje = 'No puedes eliminar este recurso.'; return; }
        if ($recurso->path && \Storage::disk('public')->exists($recurso->path)) {
            \Storage::disk('public')->delete($recurso->path);
        }
        $recurso->delete();
        $this->mensaje = 'Recurso eliminado.';
        $this->cargarRecursos();
        $this->cargarIntentos();
    }

    public function render()
    {
        return view('livewire.alumno-actividad-recursos');
    }
}

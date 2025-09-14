<?php

namespace App\Livewire;

use App\Models\Actividad;
use App\Models\Docente;
use App\Models\Contenido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocenteActividades extends Component
{
    use WithFileUploads;
    public ?Docente $docente = null;
    public ?int $aula_id = null;
    public ?int $asignatura_grado_id = null;

    public array $actividades = [];
    public string $mensaje = '';

    // Formulario CRUD
    public bool $mostrarFormulario = false;
    public ?int $actividad_id = null; // null = crear, otro = editar
    public string $form_titulo = '';
    public ?string $form_descripcion = null;
    public ?string $form_inicio = null; // datetime-local
    public ?string $form_fin = null;    // datetime-local

    // Recurso opcional del docente
    public $archivoDocente; // Livewire temp file
    public ?string $recursoTitulo = null;

    // Gestión de recursos (docente) por actividad
    public array $recursosActividad = [];
    public ?int $editRecursoId = null;
    public ?string $editRecursoTitulo = null;

    protected function rules(): array
    {
        return [
            'form_titulo' => ['required','string','max:255'],
            'form_descripcion' => ['nullable','string','max:500'],
            'form_inicio' => ['required','date'],
            'form_fin' => ['required','date','after_or_equal:form_inicio'],
            'archivoDocente' => ['nullable','file','max:20480','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp3,mp4,avi,mov,mpeg,ogg,webm,zip,rar,7z,jpg,jpeg,png'],
            'recursoTitulo' => ['nullable','string','max:255'],
        ];
    }

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }
        $this->docente = Docente::where('user_id', $user->id)->first() ?? Docente::where('correo', $user->email)->first();
        if (!$this->docente) { $this->mensaje = 'Tu usuario no está vinculado a un Docente.'; return; }

        $this->aula_id = request()->integer('aula_id');
        $this->asignatura_grado_id = request()->integer('asignatura_grado_id');

        $this->cargarActividades();

        // Abrir formulario de creación si viene indicado desde Mis Cursos
        if (request()->boolean('crear')) {
            $this->nueva();
            // Prefill fechas a ahora y +1 hora
            $now = now();
            $this->form_inicio = $now->format('Y-m-d\\TH:i');
            $this->form_fin = $now->copy()->addHour()->format('Y-m-d\\TH:i');
        }
    }

    protected function cargarActividades(): void
    {
        if (!$this->aula_id || !$this->asignatura_grado_id) { $this->actividades = []; return; }
        $this->actividades = Actividad::where('docente_id', $this->docente->id)
            ->where('aula_id', $this->aula_id)
            ->where('asignatura_grado_id', $this->asignatura_grado_id)
            ->orderByDesc('inicio')->orderByDesc('id')
            ->get(['id','actividad','inicio','fin'])
            ->map(fn($a) => [
                'id' => $a->id,
                'titulo' => $a->actividad,
                'inicio' => $a->inicio,
                'fin' => $a->fin,
            ])->toArray();
    }

    public function irCalificar($actividad_id)
    {
        return redirect()->route('docente.actividad.calificar', ['actividad_id' => $actividad_id]);
    }

    // CRUD
    public function nueva(): void
    {
        $this->resetFormulario();
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $act = Actividad::where('id', $id)
            ->where('docente_id', $this->docente->id)
            ->where('aula_id', $this->aula_id)
            ->where('asignatura_grado_id', $this->asignatura_grado_id)
            ->first();
        if (!$act) { $this->mensaje = 'Actividad no encontrada.'; return; }
        $this->actividad_id = $act->id;
        $this->form_titulo = $act->actividad ?? '';
        $this->form_descripcion = $act->descripcion;
        $this->form_inicio = $act->inicio ? date('Y-m-d\TH:i', strtotime($act->inicio)) : null;
        $this->form_fin = $act->fin ? date('Y-m-d\TH:i', strtotime($act->fin)) : null;
        $this->mostrarFormulario = true;
    }

    public function guardar(): void
    {
        if (!$this->aula_id || !$this->asignatura_grado_id) { $this->mensaje = 'Curso no definido.'; return; }
        $this->validate();
        $user = Auth::user();
        if (!$user) { $this->mensaje = 'No autenticado'; return; }

        if ($this->actividad_id) {
            // Update
            $act = Actividad::where('id', $this->actividad_id)
                ->where('docente_id', $this->docente->id)
                ->first();
            if (!$act) { $this->mensaje = 'Actividad no encontrada.'; return; }
            $act->update([
                'actividad' => $this->form_titulo,
                'descripcion' => $this->form_descripcion,
                'inicio' => $this->form_inicio ? date('Y-m-d H:i:s', strtotime($this->form_inicio)) : null,
                'fin' => $this->form_fin ? date('Y-m-d H:i:s', strtotime($this->form_fin)) : null,
                'observacion' => null,
            ]);
            $this->mensaje = 'Actividad actualizada correctamente.';
            // Si hay archivo, subirlo y vincular a la actividad
            if ($this->archivoDocente) {
                $this->guardarRecursoDocente($act->id);
            }
            $this->cargarRecursosActividad($act->id);
        } else {
            // Create
            $act = Actividad::create([
                'actividad' => $this->form_titulo,
                'descripcion' => $this->form_descripcion,
                'inicio' => $this->form_inicio ? date('Y-m-d H:i:s', strtotime($this->form_inicio)) : null,
                'fin' => $this->form_fin ? date('Y-m-d H:i:s', strtotime($this->form_fin)) : null,
                'usuario_id' => $user->id,
                'observacion' => null,
                'docente_id' => $this->docente->id,
                'aula_id' => $this->aula_id,
                'asignatura_grado_id' => $this->asignatura_grado_id,
            ]);
            $this->mensaje = 'Actividad creada correctamente.';
            if ($this->archivoDocente) {
                $this->guardarRecursoDocente($act->id);
            }
        }
        $this->mostrarFormulario = false;
        $this->resetFormulario();
        $this->cargarActividades();
    }

    protected function guardarRecursoDocente(int $actividadId): void
    {
        $original = $this->archivoDocente->getClientOriginalName();
        $name = uniqid('rec_')."_".$original;
        $path = $this->archivoDocente->storeAs('actividad_recursos/'.$actividadId, $name, 'public');
        $tituloBase = $this->recursoTitulo ?: $original;
        $tituloUnico = $tituloBase.' ['.now()->format('Y-m-d H:i:s').']';

        // Determinar tipo_contenido_id por extensión básica
        $ext = strtolower($this->archivoDocente->getClientOriginalExtension());
        $tipoContenidoId = match (true) {
            in_array($ext, ['doc','docx','pdf','txt','odt','rtf']) => 1,
            in_array($ext, ['ppt','pptx','odp','pps','ppsx']) => 2,
            in_array($ext, ['mp3','wav','ogg','wma','m4a','midi','opus']) => 3,
            in_array($ext, ['jpeg','jpg','tiff','bmp','png','gif','svg','ico']) => 4,
            in_array($ext, ['avi','mp4','mkv','mpeg','webm']) => 5,
            in_array($ext, ['exe','msi','dmg','pkg','apk']) => 6,
            default => 7,
        };

        $recurso = new Contenido();
        $recurso->contenido = $tituloUnico;
        $recurso->tipo_contenido_id = $tipoContenidoId;
        $recurso->usuario_id = Auth::id();
        $recurso->actividad_id = $actividadId;
        $recurso->alumno_id = null; // Docente
        $recurso->path = $path;
        $recurso->save();
    }

    public function subirSoloRecurso(): void
    {
        if (!$this->actividad_id) { $this->mensaje = 'Primero selecciona o crea una actividad.'; return; }
        $this->validateOnly('archivoDocente');
        $this->guardarRecursoDocente($this->actividad_id);
        $this->archivoDocente = null;
        $this->recursoTitulo = null;
        $this->mensaje = 'Recurso del docente subido.';
        $this->cargarRecursosActividad($this->actividad_id);
    }

    public function confirmarEliminar(int $id): void
    {
        $this->actividad_id = $id;
        $this->mensaje = 'Confirma eliminar la actividad seleccionada.';
    }

    public function eliminar(): void
    {
        if (!$this->actividad_id) { return; }
        $act = Actividad::where('id', $this->actividad_id)
            ->where('docente_id', $this->docente->id)
            ->first();
        if ($act) { $act->delete(); }
        $this->mensaje = 'Actividad eliminada.';
        $this->actividad_id = null;
        $this->cargarActividades();
    }

    public function cancelarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetFormulario();
    }

    protected function resetFormulario(): void
    {
        $this->actividad_id = null;
        $this->form_titulo = '';
        $this->form_descripcion = null;
        $this->form_inicio = null;
        $this->form_fin = null;
        $this->archivoDocente = null;
        $this->recursoTitulo = null;
    }

    protected function cargarRecursosActividad(int $actividadId): void
    {
        $this->recursosActividad = \App\Models\Contenido::where('actividad_id', $actividadId)
            ->whereNull('alumno_id') // solo del docente
            ->orderByDesc('id')
            ->get(['id','contenido','path','created_at'])
            ->map(fn($r) => [
                'id' => $r->id,
                'titulo' => $r->contenido,
                'path' => $r->path,
                'fecha' => $r->created_at,
            ])->toArray();
    }

    public function iniciarEditarRecurso(int $recursoId): void
    {
        $this->editRecursoId = $recursoId;
        $this->editRecursoTitulo = \App\Models\Contenido::where('id', $recursoId)->value('contenido');
    }

    public function guardarRecursoEditado(): void
    {
        if (!$this->actividad_id || !$this->editRecursoId) { return; }
        $this->validateOnly('editRecursoTitulo', ['editRecursoTitulo' => ['required','string','max:255']]);
        \App\Models\Contenido::where('id', $this->editRecursoId)
            ->update(['contenido' => $this->editRecursoTitulo]);
        $this->mensaje = 'Recurso actualizado.';
        $this->editRecursoId = null;
        $this->editRecursoTitulo = null;
        $this->cargarRecursosActividad($this->actividad_id);
    }

    public function eliminarRecurso(int $recursoId): void
    {
        if (!$this->actividad_id) { return; }
        $rec = \App\Models\Contenido::where('id', $recursoId)->first();
        if ($rec) {
            if ($rec->path && Storage::disk('public')->exists($rec->path)) {
                Storage::disk('public')->delete($rec->path);
            }
            $rec->delete();
        }
        $this->mensaje = 'Recurso eliminado.';
        $this->cargarRecursosActividad($this->actividad_id);
    }

    public function render()
    {
        return view('livewire.docente-actividades');
    }
}

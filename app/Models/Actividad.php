<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actividad extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'actividad',
        'descripcion',
        'inicio',
        'fin',
        'usuario_id',
        'observacion',
        'docente_id',
        'aula_id',
        'asignatura_grado_id',
    ];

    static public function camposTabla(){
        return [
            ['ID', 'at', ['id']],
            ['Título', 'at', ['actividad']],
            ['Descripción', 'at', ['descripcion']],
            ['Comienza', 'at', ['inicio']],
            ['Termina', 'at', ['fin']],
            ['Creador', 'fk', ['usuario' => ['nombre_1', 'apellido_1']]],
        ];
    }

    static public function camposModificables(){
        return [
            'actividad',
            'descripcion',
            'inicio',
            'fin',
            'usuario_id',
            'observacion'
        ];
    }

    static public function camposNoModificables(){
        return [
            'created_at',
            'updated_at',
        ];
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

    public function docente(): BelongsTo {
        return $this->belongsTo(Docente::class);
    }

    public function aula(): BelongsTo {
        return $this->belongsTo(Aula::class);
    }

    public function asignaturaGrado(): BelongsTo {
        return $this->belongsTo(AsignaturaGrado::class);
    }
}
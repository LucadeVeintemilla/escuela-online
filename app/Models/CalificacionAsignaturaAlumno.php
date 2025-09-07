<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalificacionAsignaturaAlumno extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'alumno_id',
        'asignatura_grado_id',
        'calificacion_id',
        'observacion',
    ];

    static public function vistaCampos(){
        return [
            'principales' => [
                'id' => 'ID',
            ],
            'secundarios' => [
                'observacion' => 'Observación',
            ],
            'foraneos' => [
                'usuario' => ['nombre_1' => 'Nombre',
                              'nombre_2' => 'Segundo nombre',
                              'apellido_1' => 'Apellido',
                              'apellido_2' => 'Segundo apellido'],
                'asignatura' => ['asignatura' => 'Asignatura'],
                'grado' => ['grado' => 'Grado'],
                'calificacion' => ['calificacion' => 'Calificación'],
            ],
            'timeStamps' => [
                'created_at' => 'Creación',
                'updated_at' => 'Actualización',
                // 'deleted_at' => 'Eliminación'
            ]
        ];
    }

    // Estructura para la Tabla/Fila
    static public function camposTabla(){
        return [
            ['ID', 'at', ['id']],
            ['Alumno', 'fk', ['alumno' => ['nombre_1', 'apellido_1']]],
            ['Asignatura', 'fk', ['asignatura' => ['asignatura']]],
            ['Grado', 'fk', ['grado' => ['grado']]],
            ['Calificación', 'fk', ['calificacion' => ['calificacion']]],
            ['Observación', 'at', ['observacion']],
        ];
    }

    static public function camposModificables(){
        return [
            'alumno_id',
            'asignatura_grado_id',
            'calificacion_id',
            'observacion',
        ];
    }

    static public function camposNoModificables(){
        return [
            'created_at',
            'updated_at',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function usuario(){
        return $this->alumno->usuario();
    }

    public function AsignaturaGrado(): BelongsTo
    {
        return $this->belongsTo(AsignaturaGrado::class);
    }

    public function asignatura(){
        return $this->AsignaturaGrado->asignatura();
    }

    public function grado(){
        return $this->AsignaturaGrado->grado();
    }

    // Relación en minúscula para property access en Blade
    public function calificacion(): BelongsTo { return $this->belongsTo(Calificacion::class); }

    // Accessors para soportar $objeto->usuario, $objeto->asignatura, $objeto->grado en la vista de fila
    public function getUsuarioAttribute()
    {
        return optional($this->alumno)->usuario;
    }

    public function getAsignaturaAttribute()
    {
        return optional($this->AsignaturaGrado)->asignatura;
    }

    public function getGradoAttribute()
    {
        return optional($this->AsignaturaGrado)->grado;
    }
}
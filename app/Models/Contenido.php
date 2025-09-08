<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;

class Contenido extends Model
{
    use HasFactory;
    use SoftDeletes;

    static public function camposTabla(){
        return [
            ['ID', 'at', ['id']],
            ['Recurso', 'at', ['contenido']],
            ['Tipo de recurso', 'fk', ['tipoContenido' => ['tipo']]],
            ['Creado', 'at', ['created_at']],
            ['Actualizado', 'at', ['updated_at']],
            ['Creador', 'fk', ['usuario' => ['nombre_1', 'apellido_1']]],
        ];
    }

    static public function camposModificables(){
        return [
            'contenido',
            'tipo_contenido_id',
            'observacion',
            'usuario_id',
            'path',
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

    public function tipoContenido(): BelongsTo {
        return $this->belongsTo(related: TipoContenido::class);
    }

    // Nuevas relaciones
    public function actividad(): EloquentBelongsTo {
        return $this->belongsTo(Actividad::class);
    }

    public function alumno(): EloquentBelongsTo {
        return $this->belongsTo(Alumno::class);
    }
}
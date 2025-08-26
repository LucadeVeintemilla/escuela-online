<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Docente extends Model
{
    use HasFactory;
    use SoftDeletes;

    static public function camposTabla(){
        return [
            ['ID', 'at', ['id']],
            ['Nombres y apellidos', 'at', ['nombre_1', 'nombre_2', 'apellido_1', 'apellido_2']],
            ['Correo', 'at', ['correo']],
            ['Celular', 'at', ['celular']],
            ['Aula', 'fk', ['grado' => ['abreviatura'], 'seccion' => ['seccion']], 'aula_id',],
        ];
    }

    static public function camposModificables(){
        return [
            'nombre_1',
            'nombre_2',
            'apellido_1',
            'apellido_2',
            'dni',
            'genero_id',
            'observacion',
            'correo',
            'celular',
            'aula_id'
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

    public function contacto(): BelongsTo {
        return $this->belongsTo(Contacto::class);
    }

    public function genero(){
        return $this->usuario->genero();
    }

    public function aula(): BelongsTo{
        return $this->belongsTo(Aula::class);
    }

    public function grado() {
        // Siempre devolver una instancia de Relationship
        if ($this->aula()->exists()) {
            return $this->aula->grado();
        }
        // Dummy relation que no devuelve resultados pero cumple con el contrato
        return $this->belongsTo(Grado::class, 'grado_id', 'id')->whereRaw('1=0');
    }

    public function seccion() {
        if ($this->aula()->exists()) {
            return $this->aula->seccion();
        }
        return $this->belongsTo(Seccion::class, 'seccion_id', 'id')->whereRaw('1=0');
    }
}
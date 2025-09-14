<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AlumnoInicio extends Component
{
    public array $anuncios = [];

    public function mount(): void
    {
        $now = now();
        $rows = DB::table('anuncios as a')
            ->leftJoin('anuncio_alumno_generals as aag', 'aag.anuncio_id', '=', 'a.id')
            ->leftJoin('anuncio_generals as ag', 'ag.anuncio_id', '=', 'a.id')
            ->where('a.activo', 1)
            ->where(function($q) use ($now){
                $q->whereNull('a.inicio')->orWhere('a.inicio', '<=', $now);
            })
            ->where(function($q) use ($now){
                $q->whereNull('a.fin')->orWhere('a.fin', '>=', $now);
            })
            ->whereNull('a.deleted_at')
            ->where(function($q){
                $q->whereNotNull('aag.anuncio_id')->orWhereNotNull('ag.anuncio_id');
            })
            ->orderByDesc('a.created_at')
            ->select(['a.id','a.anuncio','a.descripcion','a.inicio','a.fin','a.created_at'])
            ->distinct()
            ->limit(50)
            ->get();
        $this->anuncios = $rows->map(fn($r) => [
            'id' => $r->id,
            'titulo' => $r->anuncio,
            'descripcion' => $r->descripcion,
            'inicio' => $r->inicio,
            'fin' => $r->fin,
            'fecha' => $r->created_at,
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.components.alumno-inicio');
    }
}

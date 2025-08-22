<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControleAcessoRecurso extends Model
{
    //
    protected $table  = 'pessoas_controle_acessos';
    public $timestamps = false;

    public function pessoa(){
    	return $this->belongsTo('App\Models\Pessoa','pessoa'); // (Pessoa::class)
    }
    public function recurso(){
    	return $this->hasOne('App\RecursoSistema','recurso'); // (Pessoa::class)
    }
}

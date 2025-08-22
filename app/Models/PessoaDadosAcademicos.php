<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoaDadosAcademicos extends Model
{
    //
    protected $table  = 'pessoas_dados_academicos';
     
    public function pessoa(){
		return $this->belongsTo('App\Models\Pessoa');
	}
	public function dado(){
		return $this->hasOne('App\TipoDado');
	}
}

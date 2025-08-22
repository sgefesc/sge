<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoaDadosGerais extends Model
{
    //
    protected $table  = 'pessoas_dados_gerais';

    public function pessoa(){
		return $this->belongsTo('App\Models\Pessoa');
	}
	public function dado(){
		return $this->hasOne('App\TipoDado');
	}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Transferencia extends Model
{
    //use SoftDeletes;
    public $timestamps = false;

    public function matricula(){
    	return $this->hasOne('App\Models\Matricula');
    }

    public function anterior(){
    	return $this->hasOne('App\Models\Inscricao');
    }

    public function nova(){
    	return $this->hasOne('App\Models\Inscricao');
    }

    public function responsavel(){
    	return $this->hasOne('App\Models\Pessoa');
    }
     public function getAnterior(){
        $this->anterior = \App\Models\Models\Inscricao::find($this->anterior);
        
     }
     public function getNova(){
        $this->nova = \App\Models\Models\Inscricao::find($this->nova);
        
     }
     public function getMatricula(){
        $this->matricula = \App\Models\Models\Matricula::find($this->matricula);
     }
     public function getPessoa(){
        $this->getMatricula();
        $matricula = $this->matricula;
        $pessoa = $matricula->pessoa;
        return \App\Models\Models\Pessoa::find($pessoa);
     }

}

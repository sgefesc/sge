<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    public function curso(){
    	return $this->hasMany('App\Curso','curso');
    }

    public function disciplina(){
    	return $this->hasMany('App\Disciplina','disciplina');
    }

}

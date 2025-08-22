<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    //
    public function curso(){
    	return $this->belongsToThrough(Curso::Class,CursoRequisito::Class);
    }
}

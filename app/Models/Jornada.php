<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jornada extends Model
{
    public $timestamps = false;
	//protected $dates = ['inicio','termino'];
	protected $casts = [
    'inicio' => 'date',
	'termino' => 'date',
    ];

    public function setDiasSemanaAttribute($value){
		$this->attributes['dias_semana']= implode(',',$value);
	}
	public function getDiasSemanaAttribute($value){
		return explode(',',$value);
	}

	public function getLocal()
	{
		$sala = \App\Models\Models\Sala::find($this->sala);
		$local = \App\Models\Models\Local::find($sala->local);

		return $local;

	}
	public function getPessoa(){

		$pessoa = \App\Models\Models\Pessoa::withTrashed()->find($this->pessoa);
		return $pessoa;
	}

	public function getHoraInicioAttribute($value){	
		return substr($value,0,5);
	}
	public function getHoraTerminoAttribute($value){	
		return substr($value,0,5);
	}
}

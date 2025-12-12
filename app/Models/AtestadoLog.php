<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtestadoLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    //protected $dates = ['data'];
    //fields: id, atestado, evento, data, pessoa
    protected $casts = [
    'data' => 'date',
    ];


    public function getNomeResponsavel(){
		return \App\Models\Pessoa::getNome($this->pessoa,'simples');
	}

    public static function registrar($codigo,$evento,$autor=0){
        $log = AtestadoLog::where('atestado',$codigo)->where('evento',$evento)->where('pessoa',$autor)->first();
        if(is_null($log)){
            $log = new AtestadoLog;
            $log->atestado = $codigo;
            $log->evento = $evento;
            $log->data = date('Y-m-d H:i');
            $log->pessoa = $autor;
            $log->save();
            return;
        }
        else
            return $log;

    }

	
}

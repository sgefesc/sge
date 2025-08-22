<?php

namespace App\Models;
use App\Models\Pessoa;


use Illuminate\Database\Eloquent\Model;

class PessoaDadosJornadas extends Model
{
   
    protected $table  = 'pessoas_dados_jornadas';
    public $timestamps = false;
    //protected $dates = ['inicio','termino'];
    protected $casts = [
    'inicio' => 'date',
	'termino' => 'date',
    ];

    public static function getCarga(int $pessoa){
        $carga = PessoaDadosJornadas::where('pessoa',$pessoa)->orderByDesc('id')->first();
        return $carga->carga;
    }
    
    public function getPessoa(){
        $pessoa = Pessoa::find($this->pessoa);
        return $pessoa;
    }
    
}

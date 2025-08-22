<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoaDadosContato extends Model
{
    //
    protected $table  = 'pessoas_dados_contato';

    public function pessoa(){
    	return $this->belongsTo('App\Models\Pessoa','pessoa'); // (Pessoa::class)
    }
    public function dado(){
    	return $this->hasOne('App\TipoDado','dado'); // (Pessoa::class)
    }
    public static function getTelefone($id){
    	$telefones = PessoaDadosContato::where('pessoa',$id)->whereIn('dado',[2,9])->where('valor','<>','')->orderByDesc('id')->limit(3)->get();
    	return $telefones;

    }



}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PessoaDadosAdministrativos extends Model
{
    //
    use SoftDeletes;
    //protected $dates = ['deleted_at'];
	protected $casts = [
    'deleted_at' => 'date',
    ];

	protected $table  = 'pessoas_dados_administrativos';

    public function pessoa(){
		return $this->belongsTo('App\Models\Pessoa');
	}
	public function dado(){
		return $this->hasOne('App\TipoDado');
	}

	public static function getFuncionarios($categoria=''){
		if($categoria=='')
			$funcionarios_ri=PessoaDadosAdministrativos::where('dado','relacao_institucional')->get();
		elseif (is_array($categoria)) {
			$funcionarios_ri=PessoaDadosAdministrativos::where('dado','relacao_institucional')->whereIn('valor', $categoria)->get();
		}
		else
			$funcionarios_ri=PessoaDadosAdministrativos::where('dado','relacao_institucional')->where('valor', $categoria)->get();

		$funcionarios=collect();
		foreach($funcionarios_ri as $dado){
			$pessoa = Pessoa::withTrashed()->find($dado->pessoa);
			$funcionarios->push($pessoa);
		}
		$funcionarios = $funcionarios->sortBy('nome');

		return $funcionarios;
	}

	public static function getEducadores(){
		return PessoaDadosAdministrativos::getFuncionarios(['Educador','Educador de Parceria','Educador Temporário','Educador Terceirizado']);
	}

	public static function cadastrarUnico(int $pessoa,$dado,$valor){

		$busca = PessoaDadosAdministrativos::where('pessoa',$pessoa)->where('dado',$dado)->where('valor',$valor)->first();
		if($busca == null){
			$informacao = new PessoaDadosAdministrativos;
			$informacao->pessoa = $pessoa;
			$informacao->dado = $dado;
			$informacao->valor = $valor;
			$informacao->save();
			return $informacao;
		}
		else
			return $busca;

	}


	public static function addPendencia($pessoa, $descricao){
		return PessoaDadosAdministrativos::cadastrarUnico($pessoa,'pendencia',$descricao);
	}



}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bolsa extends Model
{
    //
    use SoftDeletes;
    protected $appends=['desconto'];

	public function getDescontoAttribute($value){
		$valor = \App\Models\Desconto::find($value);
		
			return $valor;

	}
    public function desconto(){
		return $this->hasOne('App\Desconto','desconto'); // (Pessoa::class)
	}

	public function getNomeCurso($matricula){
		$matricula = \App\Models\Matricula::find($matricula);
		if($matricula)
			return $matricula->getNomeCurso();
		else
			return "Erro ao obter nome do curso.";
	
	}

	public function getPessoa(){
		$pessoa = Pessoa::withTrashed()->find($this->pessoa);
		if($pessoa!=null)
			return $pessoa;
		else
			return "Nome não encontrado.";
	}
	

	public function getMatriculas(){
		$matriculas = BolsaMatricula::where('bolsa',$this->id)->get();
		return $matriculas;
	}

	public function getTipo(){
		$tipo = Desconto::find($this->desconto);
	
	}

	public function getPrograma(){
		$programa = array();
		$bolsa_matriculas = $this->getMatriculas();		
		foreach($bolsa_matriculas as $bolsa_matricula){
			$matricula = Matricula::find($bolsa_matricula->matricula);
			if(!in_array($matricula->getPrograma()->sigla, $programa))
				$programa[] = $matricula->getPrograma()->sigla; 


		}
		return $programa;
	}

	/**
	 * Função para obter o link de download ou visualização do arquivo
	 * $tipo: requerimento ou parecer
	 * 
	 */
	public function getLink(string $tipo) {
		if($tipo == 'parecer'){
			$path = 'documentos/bolsas/pareceres/'.$this->id.'.pdf';
		}
		else{
			$path = 'documentos/bolsas/requerimentos/'.$this->id.'.pdf';
		}

		if(\Illuminate\Support\Facades\Storage::exists($path)){
			return $path;
		}
		else
			return null;
	}
}

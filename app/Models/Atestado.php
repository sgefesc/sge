<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atestado extends Model
{
    use SoftDeletes;
    //protected $dates = ['emissao','validade','created_at','deleted_at'];
	protected $casts = [
    'emissao' => 'date',
	'validade' => 'date',
	'created_at' => 'date',
	'deleted_at' => 'date',
    ];
   
	/**
	 * The attributes that are mass assignable.
	 * @param int $sala
	 * @return string
	 */
    public function calcularVencimento(int $sala){
    	if($sala == 6) //se for piscina
    		$validade = "+6 months";
    	else
    		$validade = "+12 months";

    	if($this->emissao == null)
    		return $this->validade;
    	else
    		$vencimento = date('Y-m-d 23:23:59', strtotime($validade,strtotime($this->emissao))); 

    	return $vencimento;

    }

	/**
	 * Verifica se o atestado está vencido
	 * @param int $turma
	 * @return bool vencido ou não
	 */
	public function verificaPorTurma(int $turma){

		$turma = \App\Models\Turma::find($turma);
		if($turma == null)
			return false;
		if($this->calcularVencimento($turma->sala) < date('Y-m-d 23:23:59')){
			return false;
		}	
		else
			return true;

	}

	public function getNome(){
		return \App\Models\Pessoa::getNome($this->pessoa);
	}

	public function validar(){
		$validade = $this->calcularVencimento(0);
		if($validade < date('Y-m-d 23:23:59'))
			return true;
		else
			return false;
	}

	public static function verificarPessoa(int $pessoa, int $sala = 0){
		$atestado = Atestado::where('pessoa', $pessoa)
			->where('status', 'aprovado')
			->where('tipo','saude')
			->orderBy('id', 'desc')
			->first();
		
		if($atestado == null)
			return false;
		else{
			return true;

		}

		

		
	}

	public function getDownloadLink(){
		if(\Illuminate\Support\Facades\Storage::exists('documentos/atestados/'.$this->id.'.pdf'))
			return asset('/download/atestado/'.$this->id);
		else
			return null;
	}

	public function getViewLink(){
		if(\Illuminate\Support\Facades\Storage::exists('documentos/atestados/'.$this->id.'.pdf'))
			return asset('/arquivo/atestado/'.$this->id);
		else
			return null;
	}


}

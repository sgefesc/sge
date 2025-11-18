<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inscricao extends Model
{
	use SoftDeletes;
	protected $table  = 'inscricoes';
    //
    public function getPessoaAttribute($value){
		$pessoa=Pessoa::withTrashed()->where('id',$value)->get(['id','nome'])->first();
		return $pessoa;
	}

	public function getTurmaAttribute($value){
		$curso=Turma::where('id',$value)->get(['id','programa','carga','curso','disciplina','professor','local','dias_semana','hora_inicio','hora_termino','data_inicio','data_termino','vagas','status','sala'])->first();
		return $curso;
	}
	public function setTurma($value){
		$this->turma = $value;
		return $this->turma;

	}
	public function getCurso(){
		$curso=Turma::where('id',$value)->get(['id','curso']);
		return $curso->curso;

	}
	public function getTransferencia(){

		$tr = Transferencia::where('anterior',$this->id)->first();
		//dd($tr);
		return $tr;


	}
	public function getAtestado(){
			$atestado = \App\Models\Atestado::where('pessoa',$this->pessoa->id)->orderByDesc('id')->first();
		
		

		return $atestado;
	}
	public static function addConceito(int $inscricao,$nota){
		$inscricao = Inscricao::find($inscricao);
		$inscricao->conceito = $nota;
		$inscricao->save();
		return true;
	}

	public function alterarStatus($status){     
		$this->status = $status;
		$this->save();
		switch($status){
			case "pendente" :
				\App\Http\Controllers\MatriculaController::atualizar($this->matricula);
				break;
			case "regular": 
				\App\Http\Controllers\TurmaController::modInscritos($this->turma->id);
				\App\Http\Controllers\MatriculaController::atualizar($this->matricula);
				break;
			case "finalizada": 
				\App\Http\Controllers\MatriculaController::atualizar($this->matricula);
				break;
			case "cancelada":
				\App\Http\Controllers\TurmaController::modInscritos($this->turma->id);
				\App\Http\Controllers\MatriculaController::atualizar($this->matricula);
				break;
			case "transferida": 
				\App\Http\Controllers\TurmaController::modInscritos($this->turma->id);
				\App\Http\Controllers\MatriculaController::atualizar($this->matricula);
				break;
		}
                    
    }


	/**
	 * Verifica se há conflito de horários entre as turmas atuais e a nova turma
	 *
	 * @param array $turmas_atuais IDs das turmas atuais do aluno
	 * @param int $nova_turma ID da nova turma a ser verificada
	 * @return bool Retorna id da turmas conflitante ou null caso contrário
	 */

	public static function verificarConflitoTurmas(array $turmas_atuais, int $nova_turma){
		$turmas_conflitantes = Array();
		$turma = Turma::find($nova_turma);
		foreach($turmas_atuais as $turma_id){
			$hora_fim=date("H:i",strtotime($turma->hora_termino." - 1 minutes"));
                    foreach($turma->dias_semana as $turm){
                        $data = \Carbon\Carbon::createFromFormat('d/m/Y', $turma->data_termino)->format('Y-m-d');
                        //listar turmas que tenham conflito de horário

                        $turmas_conflitantes = array_merge($turmas_conflitantes,
                       
                            Turma::where('dias_semana', 'like', '%'.$turm.'%')
                            ->where(function($q) use ($turma) {
                            // Verifica se os horários se sobrepõem
                            $q->where(function($subq) use ($turma) {
                                $subq->where('hora_inicio', '>=', $turma->hora_inicio)
                                    ->where('hora_inicio', '<', $turma->hora_termino);
                                })->orWhere(function($subq) use ($turma) {
                                $subq->where('hora_termino', '>', $turma->hora_inicio)

                                    ->where('hora_termino', '<=', $turma->hora_termino);
                                })->orWhere(function($subq) use ($turma) {
                                $subq->where('hora_inicio', '<=', $turma->hora_inicio)
                                    ->where('hora_termino', '>=', $turma->hora_termino);
                                });
                            })
                            ->where('data_inicio','<=',$data)
                            ->whereIn('status',['iniciada','espera'])
                            ->pluck('id')->toArray());  
                    }
		}

		foreach($turmas_conflitantes as $turma_conflitante){
			if(in_array($turma_conflitante, $turmas_atuais))
				return $turma_conflitante;
		}

		return null;
		
	}
            
        
    


}

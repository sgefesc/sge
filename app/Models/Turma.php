<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Turma extends Model
{
	use SoftDeletes;

	protected $appends=['icone_status','valor'];

	public function setDiasSemanaAttribute($value){
		$this->attributes['dias_semana']= implode(',',$value);
	}
	public function getDiasSemanaAttribute($value){
		return explode(',',$value);
	}
	public function setValorAttribute($value){
		$this->attributes['valor'] = str_replace(',', '.', $value);
	}

	public function getDados(){
		$extras = TurmaDados::where('turma',$this->id)->orderBy('id','asc')->get();
		foreach($extras as $extra){
			$this->{$extra->dado} = $extra->valor;
		}
		return $extras;
	}


	/*
	Valor que vai aparecer na lista de turmas
	*/
	public function getValorAttribute($value){
		//return $this->getRawOriginal('valor');

		//verifica se o curso é fora da fesc, se for, retorna valor 0
		$fesc=[84,85,86,118];
		if(!in_array($this->local->id,$fesc)){
			return 0;
		}

		//******************************************* inauguraçao CT Gamer
		$sala = $this->getSala();
		if(isset($sala->id) &&  $sala->id == 103 && date('Y')==2025)
			return 0;


		//verifica se não é EMG, se for retorna valor 0
		if($this->programa->id == 4)
			return 0;

		//verifica se é parceria social
		if($this->parceria)
			return 0;
		
		// se for do curso atividades uati
		if($this->curso->id == 307 && $this->carga<10)
		{
			//mostra valor de 1 disciplina
			$valor= Valor::where('curso',307)->where('carga',1)->where('ano',substr($this->data_inicio,-4));
		}
		elseif($this->getRawOriginal('valor') == 0)
		{	
			//procura curso/carga/ano.
			$valorc= Valor::where('curso',$this->curso->id)->where('carga',$this->carga)->where('ano',substr($this->data_inicio,-4))->get();

			if($valorc->count()!=1)

					//procura curso/ano
					$valorc= Valor::where('curso',$this->curso->id)->where('ano',substr($this->data_inicio,-4))->get();

			if($valorc->count()!=1)

				//programa/carga/ano
				$valorc= Valor::where('programa',$this->programa->id)->where('carga',$this->carga)->where('ano',substr($this->data_inicio,-4))->get();

			if($valorc->count()!=1)

				//se não tiver na tabela, pega do valor da tabela turma mesmo;
				return $value;

			$valor=$valorc->first();
				
			
		}
		else{
			return $this->getRawOriginal('valor');
		}
		

		if(isset($valor->valor))
			return $valor->valor;
		else{
			return 0;
			//dd("Ops. Turma ".$this->id." não está com valor definido. As inscrições para ela estão suspensas.");
		}

	}

	public function getParcelas(){
		//procura curso/carga/ano.
		$valorc= Valor::where('curso',$this->curso->id)->where('carga',$this->carga)->where('ano',substr($this->data_inicio,-4))->get();
		if($valorc->count()!=1)
				$valorc = Valor::where('curso',$this->curso->id)
							->where('ano',substr($this->data_inicio,-4))
							->get();

		if($valorc->count()!=1)
			$valorc = Valor::where('programa',$this->programa->id)
							->where('carga',$this->carga)->where('ano',substr($this->data_inicio,-4))->get();

		if($valorc->count()!=1){
			if($this->parcelas == 0){
				//se não tiver na tabela, pega do valor da tabela turma mesmo;
				try{
				$primeira_parcela = $this->getDataPrimeiraParcela();
				$dt_i=Carbon::createFromFormat('d/m/Y', $primeira_parcela->format('d/m/Y'));
				$dt_t=Carbon::createFromFormat('d/m/Y', $this->data_termino);
				$diference=$dt_i->diffInMonths($dt_t);
				$diference++;
				}
				catch(\Exception $e){
					echo $e->getMessage();
					return 0;
				}
				return $diference;
			}
			else
				return $this->parcelas;
		}
		else
			return $valorc->first()->parcelas;
				

		
	}

	/**
	 * Função para pegar a data de início da primeira parcela
	 * Se data de inicio do curso > 10 fica pro mes seguinte ao início senão fica para o mesmo mês
	 * @param  Turma $turma [turma que será usada para pegar a data de início]
	 * @return \Datetime [data de início da primeira parcela]
	 */
	public function getDataPrimeiraParcela():\Datetime {
		$inicio_curso = \DateTime::createFromFormat('d/m/Y', $this->data_inicio);
	
		if($inicio_curso->format('d') > env('DATA_CORTE'))
			$inicio_curso = \DateTime::createFromFormat('d/m/Y', '10/' . $inicio_curso->format('m')+1 . '/' . $inicio_curso->format('Y'));
		else
			$inicio_curso = \DateTime::createFromFormat('d/m/Y', '10/' . $inicio_curso->format('m') . '/' . $inicio_curso->format('Y'));

		return $inicio_curso;

	}


	public function setAtributosAttribute($value){
		if(!empty($value))
			$this->attributes['atributos'] = implode(',',$value);	
	}
	public function getAtributosAttribute($value){
		return explode(',',$value);
	}
	public function getProfessorAttribute($value){
		$professor=Pessoa::withTrashed()->where('id',$value)->get(['id','nome'])->first();
		return $professor;
	}
	public function getDataInicioAttribute($value){
		return Carbon::parse($value)->format('d/m/Y');
	}
	public function getDataTerminoAttribute($value){
		return Carbon::parse($value)->format('d/m/Y');
	}
	public function getProgramaAttribute($value){
		return Programa::find($value);
	}
	public function getCursoAttribute($value){
		$curso=Curso::where('id',$value)->get(['id','nome','carga'])->first();
	
		return $curso;
	}
	public function getDisciplinaAttribute($value){
		$disciplina=Disciplina::where('id',$value)->get(['id','nome','carga'])->first();
		
		return $disciplina;
	}
	public function getParceriaAttribute($value){	
		return Parceria::find($value);
	}
	public function getLocalAttribute($value){	
		return Local::find($value);
	}
	public function getHoraInicioAttribute($value){	
		return substr($value,0,5);
	}
	public function getHoraTerminoAttribute($value){	
		return substr($value,0,5);
	}
	
	public function getIconeStatusAttribute($value){
		switch($this->status){
			case 'cancelada':
				return "ban";
				break;
			case 'lancada':
				return "clock-o";
				break;
			case 'iniciada':
				return "check-circle-o";
				break;
			case 'encerrada':
				return "minus-circle";
				break;
			default:
				return "question-circle";
				break;
		}//end switch
	}


	public function getTempoCurso(){
		$dt_i=Carbon::createFromFormat('d/m/Y', $this->data_inicio);
		$dt_t=Carbon::createFromFormat('d/m/Y', $this->data_termino);
		$diference=$dt_i->diffInMonths($dt_t);
		$diference++;
		return $diference;

	}

	public function getInscricoes($tipo){
		if(is_array($tipo))
			$this->inscricoes = $inscricoes = Inscricao::where('turma',$this->id)->whereIn('status',$tipo)->get();
		
		elseif($tipo == null || $tipo == 'todas')
			$this->inscricoes = $inscricoes = Inscricao::where('turma',$this->id)->get();
		else 
			$this->inscricoes = $inscricoes = Inscricao::where('turma',$this->id)->where('status','regular')->get();

		$this->inscricoes = $this->inscricoes->sortBy('pessoa.nome');

		return $this->inscricoes;
		
		
	}

	public function getNomeCurso(){
		if(isset($this->disciplina))
                return $this->disciplina->nome;
            else
                return $this->curso->nome;
	}
	
	public function getSala(){
		$sala = Sala::find($this->sala);
		return $sala->id;
	}

	public function atualizarInscritos($num){
		$this->matriculados = $num;
		$this->save();
		return $num;
	}

	public function getAulas(){
		$this->aulas = Aula::where('turma',$this->id)->orderBy('data')->get();
		return $this->aulas;
	}

	public function getAulasDadas($mes){
		if(is_null($mes))
			$this->aulas = Aula::where('turma',$this->id)->where('status','executada')->orderBy('data')->get();
		else
			$this->aulas = Aula::where('turma',$this->id)->whereMonth('data',$mes)->where('status','executada')->orderBy('data')->get();
		return $this->aulas;
	}

	public function loadDadosTurma(){
		$dados = TurmaDados::where('id', $this->id)->get();
		foreach($dados as $dado){
			$this->$dado->dado = $dado->valor;
		}
		return $dados;
	}

	public function getVagasEmg(){
		$mista = TurmaDados::where('turma', $this->id)->where('dado','mista_emg')->first();
		if($mista){
			$vagas = TurmaDados::where('turma', $this->id)->where('dado','vagas_emg')->first();
			$inscritos = TurmaDados::where('turma', $this->id)->where('dado','inscritos_emg')->first();
			if($vagas && $inscritos){
				return $vagas->valor - $inscritos->valor;
			}
			elseif($vagas){
				return $vagas->valor;
			}
			else{
				return $this->vagas;
			}
		}
		else{
			return -1;
		}
			


	}

	public function getConceitos($tipo = 'ca'){
		$num_inscricoes = Inscricao::where('turma',$this->id)->where('conceito',strtoupper($tipo))->where('status','finalizada')->count();
		return $num_inscricoes;

	}

	public function getFichaTecnica(){
		$ficha = \App\Models\FichaTecnica::select('id')->where('turma',$this->id)->first();
		if($ficha)
			return $ficha->id;
		else
			return null;
	}

	public function verificaRequisitos($aluno, $relatorio = false){
	
		
		$aluno = Pessoa::find($aluno);
		if($aluno == null){
			if($relatorio){
				$retorno = new \stdClass;
				$retorno->status = false;
				$retorno->msg = "Aluno não encontrado na função de validação.";
				return $retorno;

			}
			else
				return false;

		}		
		
		$atestado = 0;
		$idade_minima = 0;
		$idade_maxima = 0;
		
		$reqs = CursoRequisito::where('para_tipo','turma')->where('curso',$this->id)->get();
		foreach($reqs as $req){
			switch($req->requisito->id){
				case 22:
					$idade_minima = 40;
					break;
				case 23:
					$idade_minima = 16;
					break;
				case 24:
					$idade_minima = 40;
					$idade_maxima = 60;
					break;
				case 25:
					$idade_minima = 60;
					break;
				case 26:
					$idade_minima = 18;
					$idade_maxima = 39;
						break;
				case 21:
					$idade_minima = 14;
					break;
				case 20:
					$idade_minima = 10;
					$idade_maxima = 14;
					break;
				case 27:// piscina
				case 18:// atividade física	
					$atestado = 1;
					break;
				case 1:
					$idade_minima = 18;
					break;
			}
		}
		if(isset($this->idade_min))
			$idade_minima = $this->idade_min;

		if(isset($this->idade_max))
			$idade_maxima = $this->idade_max;
		


		if($idade_minima>0 && $idade_minima>$aluno->getIdade()){
			if($relatorio){
				$retorno = new \stdClass;
				$retorno->status = false;
				$retorno->msg = "Aluno possui ".$aluno->getIdade()." anos. A turma exige o mínimo de ".$idade_minima;
				return $retorno;

			}
			else
				return false;
		}
		
		if($idade_maxima>0 && $idade_maxima<$aluno->getIdade() ){
			if($relatorio){
				$retorno = new \stdClass;
				$retorno->status = false;
				$retorno->msg = "Aluno possui ".$aluno->getIdade()." anos. A turma permite no máximo de ".$idade_maxima;
				return $retorno;

			}
			else
				return false;
		}

		if($atestado ==1){
			$atestado_m = Atestado::where('pessoa',$aluno->id)->where('tipo','saude')->where('status','aprovado')->orderbyDesc('id')->first();
			if($atestado_m == null){
				if($relatorio){
					$retorno = new \stdClass;
					$retorno->status = false;
					$retorno->msg = "Aluno não possui atestado de saúde aprovado.";
					return $retorno;
				}
				else
					return false;
			}
			
			if($atestado_m->verificaPorTurma($this->id))
				return true;
			else{
				if($relatorio){
					$retorno = new \stdClass;
					$retorno->status = false;
					$retorno->msg = "Atestado de saúde está vencido para esta atividade.";
					return $retorno;
	
				}
				else
					return false;
			}
		}
		return true;		
	}


	public function verificaSeAtividadeFisica(){
		$reqs = CursoRequisito::where('para_tipo','turma')->where('curso',$this->id)->get();
		if($reqs->count==0)
		{
			return false;
		}
		else
		{
			if($reqs->where('requisito','27')->fisrt())
				return 6;

			if($reqs->where('requisito','18')->fisrt())
				return 12;
			
			return false;


		}

	}

}

<?php

namespace App\Models;
use App\Models\AulaDado;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aula extends Model
{
    use SoftDeletes;
    public $timestamps = false;
    //protected $dates = ['data'];
    protected $casts = [
    'data' => 'date',
    ];

    /*public function getDataAttribute($value){
        $newdata = \DateTime::createFromFormat('Y-m-d',$value);
        return $newdata;
    }*/

    public function getAlunosPresentes(){
        $presentes = array();
        $frequencias = \App\Models\Frequencia::select('aluno')->where('aula',$this->id)->get();
        foreach($frequencias as $frequencia){
            $presentes[] = $frequencia->aluno;
        }
        return $presentes;
    }

    public function getAlunosComAtestado(){
        //dd($this->data->format('Y-m-d'));
        $justificados = array();
        $todos = \App\Models\Inscricao::selectRaw('pessoa as aluno')->where('turma',$this->turma)->pluck('aluno')->toArray();    
        $atestados = Atestado::where('tipo', 'medico')->whereIn('pessoa',$todos)->where('emissao', '<=',$this->data)->where('validade','>=',$this->data)->get();
        foreach($atestados as $atestado){
            $justificados [] = $atestado->pessoa;
        }
        return $justificados;    
    }

    public function getAlunosComJustificativa(){
        return true;
    }



    public function getConteudo(){
        $conteudo = AulaDado::where('aula',$this->id)->where('dado','conteudo')->get();
        if($conteudo->count())
            return $conteudo->implode('valor','. ');
        else
            return 'Nenhum conteúdo registrado.';
    }

    public function getOcorrencia(){
        $ocorrencias = AulaDado::where('aula',$this->id)->where('dado','ocorrencia')->get();
        if($ocorrencias->count())
            return $ocorrencias->implode('valor','. ');
        else
            return 'Nenhuma ocorrência registrada.';

    }

    public function getDados($dado){
        $dados = AulaDado::where('aula',$this->id)->where('dado',$dado)->get();
        if($dados->count())
            return $dados->implode('valor','. ');
        else
            return '';

    }

    
}

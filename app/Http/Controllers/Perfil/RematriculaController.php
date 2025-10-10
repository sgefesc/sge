<?php

namespace App\Http\Controllers\Perfil;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Turma;
use App\Models\TurmaDados;
use App\Models\Inscricao;
use App\Models\Matricula;
use App\Models\MatriculaDados;

class RematriculaController extends Controller
{
   
    public function rematricula_view(Request $r){

        $devedor = \App\Models\Boleto::verificarDebitos($r->pessoa->id);
        if($devedor->count()>0)
            return redirect()->back()->withErrors(['Pendências encontradas em seu cadastro. Verifique seus boletos ou entre em contato com nossa secretaria.']);


        $pendenciasMsg=\App\Models\PessoaDadosAdministrativos::where('pessoa',$r->pessoa->id)->where('dado','pendencia')->get();
		foreach($pendenciasMsg as $pendencia){
			if($pendencia->valor == 'Dívida ativa')
				 return redirect()->back()->withErrors(['Pendência encontrada. Verifique na secretaria escolar']);
			
		
        }

        if(date('m')<8)
            $data_limite = (date('Y')-1).'-11-01';
      
        $matriculas = Matricula::where('pessoa', $r->pessoa->id)
                ->whereIn('status',['expirada','ativa'])
                ->whereDate('data','>',(date('Y')-1).'-11-01')
                ->orderBy('id','desc')->get();
        foreach($matriculas as $matricula){
            $matricula->inscricoes = Inscricao::where('matricula',$matricula->id)->whereIn('status',['regular','finalizada'])->get();
            foreach($matricula->inscricoes as $inscricao){  
                $turmas_provaveis =Turma::where('professor',$inscricao->turma->professor->id)
                                                        ->where('dias_semana',implode(',', $inscricao->turma->dias_semana))
                                                        ->where('hora_inicio',$inscricao->turma->hora_inicio)
                                                        ->where('data_inicio','>',\Carbon\Carbon::createFromFormat('d/m/Y', $inscricao->turma->data_termino)->format('Y-m-d'))                                                
                                                        ->where('status_matriculas','rematricula')
                                                        ->get();
                //dd(\Carbon\Carbon::createFromFormat('d/m/Y', $inscricao->turma->data_termino)->format('Y-m-d'));
                $alternativas = TurmaDados::where('turma',$inscricao->turma->id)->where('dado','proxima_turma')->first();
                if($alternativas){
                    $alternativas = explode(',',$alternativas->valor);
                    foreach($alternativas as $alternativa){
                        $turma = Turma::find($alternativa);
                        
                        if($turma && $turma->status_matriculas == 'rematricula'){
                            $pacote = TurmaDados::where('turma',$turma->id)->where('dado','pacote')->first();
                            if($pacote)
                                $turma->pacote = $pacote->valor;

                            $x = $inscricao->proxima_turma->where('id',$turma->id);
                            if($x->count() == 0)                                             
                                $inscricao->proxima_turma->push($turma);
                        }

                    }
                }
            }
        }
        //dd($matriculas->first()->inscricoes);
        return view('perfil.rematricula')->with('pessoa',$r->pessoa)->with('matriculas',$matriculas);
    }


    public function rematricula(Request $request){
        $inscricoes = Inscricao::where(pessoa,$request->pessoa->id)
            ->where('status','ativa')
            ->get();

        foreach($inscricoes as $inscricao){
            $turmas_continucaco = Turma::where('professor',$inscricao->turma->professor->id)
                                        ->where('dias_semana',implode(',', $inscricao->turma->dias_semana))
                                        ->where('hora_inicio',$inscricao->turma->hora_inicio)
                                        ->where('data_inicio','>',\Carbon\Carbon::createFromFormat('d/m/Y', $inscricao->turma->data_termino)->format('Y-m-d'))                                                
                                        ->where('status_matriculas','rematricula')
                                        ->get();
            $alternativas = TurmaDados::where('turma',$inscricao->turma->id)->where('dado','proxima_turma')->first();

            

        
                                    
        }

    }
}

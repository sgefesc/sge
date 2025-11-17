<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Turma;
use App\Models\Inscricao;
use App\Models\Matricula;

class PerfilMatriculaController extends Controller
{
    //
    public function matriculasAtivas(Request $r){
        $passport = \App\Models\Atestado::where('pessoa',$r->pessoa->id)->where('tipo','vacinacao')->where('status','aprovado')->first();
        $matriculas = \App\Models\Matricula::where('pessoa',$r->pessoa->id)->whereIn('status',['ativa','pendente','espera'])->get();
        foreach($matriculas as $matricula){
            $matricula->getInscricoes();
        }
        //dd($matriculas);
        return view('perfil.matriculas.matriculas')->with('pessoa',$r->pessoa)->with('matriculas',$matriculas)->with('vacinado',$passport);
    }
    public function turmasDisponiveis(Request $r){
        $devedor = \App\Models\Boleto::verificarDebitos($r->pessoa->id);
        if($devedor->count()>0)
            return redirect()->back()->withErrors(['Pendências encontradas em seu cadastro. Verifique seus boletos ou entre em contato com nossa secretaria.']);

        $pendenciasMsg=\App\Models\PessoaDadosAdministrativos::where('pessoa',$r->pessoa)->where('dado','pendencia')->get();
		foreach($pendenciasMsg as $pendencia){
			if($pendencia->valor == 'Dívida ativa')
				 return redirect()->back()->withErrors(['Pendência encontrada. Verifique na secretaria escolar']);
			
		
        }

        $turmas = Turma::whereIn('status_matriculas',['aberta','online'])->get();
        foreach($turmas as &$turma){
            $turma->nomeCurso = $turma->getNomeCurso();
            $pacote = \App\Models\TurmaDados::where('turma',$turma->id)->where('dado','pacote')->first();
            if($pacote)
                $turma->pacote = $pacote->valor;
        }
        $turmas = $turmas->sortBy('nomeCurso');
        
        //dd($turmas);
        return view('perfil.matriculas.turmas-disponiveis')->with('turmas',$turmas)->with('pessoa',$r->pessoa);

    }
    public function confirmacao(Request $r){
        if($r->turma == null)
            return redirect()->back()->withErrors(['Escolha pelo menos uma turma']);

        $turmas = Turma::whereIn('id',$r->turma)->get();
        foreach($turmas as &$turma){
            $pacote = \App\Models\TurmaDados::where('turma',$turma->id)->where('dado','pacote')->first();
            if($pacote)
                $turma->pacote = $pacote->valor;
        }
        return view('perfil.matriculas.turmas-confirma')->with('turmas',$turmas)->with('pessoa',$r->pessoa);
        
    }

    public function inscricao(Request $r){

        if($r->turma == null || $r->pessoa == null)
            return redirect()->back()->withErrors(['Escolha pelo menos uma turma']);

        $ip='';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip .='|'. $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip .='|'. $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip .='|'. $_SERVER['REMOTE_ADDR'];

        }

        

        foreach($r->turma as $turma){
            $turma_obj = Turma::find($turma);
            if($turma_obj==null)
                continue;



            $aluno = \App\Models\Pessoa::find($r->pessoa->id);
            $idade = $aluno->getIdade();
            if($idade < 18){
                $autorizacao = \App\Models\Atestado::where('pessoa',$aluno->id)->where('tipo','autorizacao_menor')->where('status','aprovado')->first();
                if($autorizacao==null){
                    \App\Models\PessoaDadosAdministrativos::addPendencia($aluno->id,'Falta autorização de responsável para a realização do curso.');
                    $status = 'pendente';
                }	
            }

            if($idade >= 60){ 
                \App\Models\PessoaDadosAdministrativos::addPendencia($aluno->id,'Confirmação de idade pendente');
                $status = 'pendente';  
            }



            $inscricao = InscricaoController::inscreverAluno($r->pessoa->id,$turma,0,$r->pessoa->id);
           
            
            if($inscricao){        
                $matricula = \App\Models\Matricula::find($inscricao->matricula);
                if(isset($status)){
                    $inscricao->status = $status;
                    $inscricao->save();
                    $matricula->status = $status;
                }
                $matricula->obs = 'Matricula online. IP: '.$ip;
                $matricula->save();
            }

            // Concede desconto de inauguração CT Gamer
            if($turma_obj->sala == 103 && date('Y')==2025){
                \App\Models\Bolsa::gerarDescontoInauguracaoCTGamer($r->pessoa->id,$matricula->id);
                
             }

        }
        
        $CC = new CarneController;
        $CC->gerarCarneIndividual($r->pessoa->id);

        return redirect('/perfil/matricula');
        
    }

    public function cancelar(Request $r, $matricula){
        $insc = \App\Models\Matricula::find($matricula);
        if($insc==null)
            return redirect()->back()->withErrors(['Inscrição não encontrada']);
        else{
            if($insc->pessoa == $r->pessoa->id){
                MatriculaController::cancelar($matricula,$insc->pessoa);
                AtendimentoController::novoAtendimento("Auto cancelamento online da matrícula ".$insc->id, $insc->pessoa,$insc->pessoa);
                return redirect('/perfil/matricula');

            }
                
            else 
                return redirect()->back()->withErrors(['Inscrição não vinculada a este usuário.']);  
        }

    }


    public function rematricula_view(Request $r){

        $devedor = \App\Models\Boleto::verificarDebitos($r->pessoa->id);
        if($devedor->count()>0)
            return redirect()->back()->withErrors(['Pendências encontradas em seu cadastro. Verifique seus boletos ou entre em contato com nossa secretaria.']);

        if(date('m')<8)
            $data_limite = (date('Y')-1).'-11-01';
        $matriculas = Matricula::where('pessoa', $r->pessoa->id)
                ->whereIn('status',['expirada','ativa'])
                ->whereDate('data','>',(date('Y')-1).'-11-01')
                ->orderBy('id','desc')->get();
        foreach($matriculas as $matricula){
            $matricula->inscricoes = \App\Models\Inscricao::where('matricula',$matricula->id)->whereIn('status',['regular','finalizada'])->get();
            foreach($matricula->inscricoes as $inscricao){  
                $inscricao->proxima_turma = \App\Models\Turma::where('professor',$inscricao->turma->professor->id)
                                                        ->where('dias_semana',implode(',', $inscricao->turma->dias_semana))
                                                        ->where('hora_inicio',$inscricao->turma->hora_inicio)
                                                        ->where('data_inicio','>',\Carbon\Carbon::createFromFormat('d/m/Y', $inscricao->turma->data_termino)->format('Y-m-d'))                                                 
                                                        ->where('status_matriculas','rematricula')
                                                        ->get();
                //dd(\Carbon\Carbon::createFromFormat('d/m/Y', $inscricao->turma->data_termino)->format('Y-m-d'));
                $alternativas = \App\Models\TurmaDados::where('turma',$inscricao->turma->id)->where('dado','proxima_turma')->first();
                if($alternativas){
                    $alternativas = explode(',',$alternativas->valor);
                    foreach($alternativas as $alternativa){
                        $turma = \App\Models\Turma::find($alternativa);
                        
                        if($turma && $turma->status_matriculas == 'rematricula'){
                            $pacote = \App\Models\TurmaDados::where('turma',$turma->id)->where('dado','pacote')->first();
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




    //
}

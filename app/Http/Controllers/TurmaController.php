<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\TurmaDados;
use App\Models\Local;
use App\Models\Programa;
use App\classes\Data;
use App\Models\PessoaDadosAdministrativos;
use App\Models\Parceria;
use App\Models\Pessoa;
use App\Models\Inscricao;
use App\Models\CursoRequisito;
use App\Models\Endereco;
use App\Models\PessoaDadosContato;
//use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class TurmaController extends Controller
{
    const DATA_LIMITE_ALTERACAO = '0330';
    /**
     * Listagem de turmas para o setor pedagógico.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $turmas = $this->listagemGlobal($request->filtro,$request->valor,$request->removefiltro,$request->remove);

        $programas=Programa::all();
        $professores=PessoaDadosAdministrativos::getEducadores();
        $professores = $professores->sortBy('nome_simples');
        $locais = Local::select(['id','sigla','nome'])->orderBy('sigla')->get();
        $periodos = \App\classes\Data::semestres();
        return view('turmas.listar-pedagogico', compact('turmas'))
            ->with('programas',$programas)
            ->with('professores', $professores)
            ->with('locais',$locais)
            ->with('filtros',session()->get('filtro_turmas'))
            ->with('periodos',$periodos);
    }


    /**
     * Pedagogico ver dados das turmas.
     * @param  [type] $turma [description]
     * @return [type]        [description]
     */
    public function mostrarTurma($turma){
        $turma=Turma::find($turma);
        if (empty($turma))
            return redirect(asset('/turmas'));
        $inscricoes=Inscricao::where('turma','=', $turma->id)->where('status','<>','cancelada')->get();
        $inscricoes->sortBy('pessoa.nome');
        
        foreach ($inscricoes as $inscricao) {
            $inscricao->telefone = \App\Models\PessoaDadosContato::getTelefone($inscricao->pessoa->id);
            $inscricao->aluno = \App\Models\Pessoa::withTrashed()->find($inscricao->pessoa->id);

            $inscricao->atestado = $inscricao->getAtestado();
            if($inscricao->atestado){
                $inscricao->atestado->validade =  $inscricao->atestado->calcularVencimento($turma->sala);
                //dd($inscricao->atestado);
            }
           
        }
        $requisitos = CursoRequisito::where('para_tipo','turma')->where('curso',$turma->id)->get();
        $aulas = \App\Models\Aula::where('turma',$turma->id)->orderBy('data')->get();
        foreach($aulas as $aula){
            //$aula->data = \DateTime::createFromFormat('Y-m-d H:i:s',$aula->data);
            switch($aula->status){
                case 'prevista': 
                    $aula->badge = 'secondary';
                    break;
                case 'planejada': 
                    $aula->badge = 'primary';
                    break;
                case 'executada': 
                    $aula->badge = 'success';
                    break;
                case 'cancelada': 
                    $aula->badge = 'danger';
                    break;
                case 'adiada': 
                    $aula->badge = 'warning';
                    break;
            }
           
        }
        
        return view('turmas.dados-pedagogico',compact('turma'))->with('inscricoes',$inscricoes)->with('requisitos',$requisitos)->with('aulas',$aulas);


    }


    /**
     * Listador global de turmas
     * Suporta os seguintes tipos de filtros:
     *     -programa
     *     -professor
     *     -local
     *     -status
     * 
     * @param  [type]  $filtro [description]
     * @param  [type]  $valor  [description]
     * @param  integer $remove [description]
     * @param  integer $ipp    [Quantidade de itens por página]
     * @return [type]          [description]
     */
    public function listagemGlobal($filtro=null,$valor=null,$rem_filtro=null,$remove=0,$ipp=50){
        
        
        //session_start();
        $sessao_filtro_turmas = session()->get('filtro_turmas');
         


        if($sessao_filtro_turmas)
            $filtros = $sessao_filtro_turmas;    
        else
            $filtros = array(); 

        if(isset($filtro) && isset($valor)){           
            if(array_key_exists($filtro, $filtros)){
                $busca = array_search($valor, $filtros[$filtro]);
                if($busca === false){
                    if($filtro == 'busca'){
                            
                            unset($filtros['busca']);
                            
                    }
                    $filtros[$filtro][] = $valor; 
                }                   
                else
                {
                    if($remove > 0){
                        unset($filtros[$filtro][$busca]);
                    }
                    
                }
            }
            else{
                $filtros[$filtro][] = $valor;
            }            
        }
        if($rem_filtro != null){
            if(isset($filtros[$rem_filtro]))
                unset($filtros[$rem_filtro]);
        } 

        //$_SESSION['filtro_turmas'] = $filtros;
        session(['filtro_turmas'=> $filtros]);
        $turmas=Turma::select('*', 'turmas.id as id' ,'turmas.vagas as vagas','turmas.carga as carga', 
            'turmas.programa as programa', 'turmas.periodicidade as periodicidade','disciplinas.id as disciplinaid','cursos.id as cursoid',
            'turmas.programa as programaid','turmas.valor as valor')
                ->join('cursos', 'turmas.curso','=','cursos.id')
                ->leftjoin('disciplinas', 'turmas.disciplina','=','disciplinas.id');

        if(isset($filtros['busca'])){
            $query = $filtros['busca'][0];
            $turmas = $turmas
            ->join('pessoas', 'pessoas.id', '=', 'turmas.professor')
            ->join('programas', 'programas.id', '=', 'turmas.programa')
            ->where(function($busca) use ($query){
                $busca->where('cursos.nome', 'like','%'.$query.'%')
                        ->orwhere('turmas.id', $query)
                        ->orwhere('disciplinas.nome', 'like','%'.$query.'%')
                        ->orwhere('pessoas.nome', 'like','%'.$query.'%')
                        ->orwhere('programas.sigla', 'like','%'.$query.'%')
                        ->orwhere('dias_semana', 'like','%'.$query.'%');
            });
        }        

        if(isset($filtros['programa']) && count($filtros['programa'])){
            $turmas = $turmas->whereIn('turmas.programa', $filtros['programa']); 
        }

        if(isset($filtros['professor']) && count($filtros['professor'])){
            $turmas = $turmas->whereIn('turmas.professor', $filtros['professor']); 
        }
        if(isset($filtros['local']) && count($filtros['local'])){
            $turmas = $turmas->whereIn('turmas.local', $filtros['local']); 
        }

        if(isset($filtros['status']) && count($filtros['status'])){
            $turmas = $turmas->whereIn('turmas.status', $filtros['status']); 
        }

        if(isset($filtros['status_matriculas']) && count($filtros['status_matriculas'])){
            $turmas = $turmas->whereIn('turmas.status_matriculas', $filtros['status_matriculas']); 
        }

        if(isset($filtros['pordata']) && count($filtros['pordata'])){
            
            $str1 = substr($filtros['pordata'][0],1,10);
            if($str1 == 'undefined'){
                $str2 = substr($filtros['pordata'][0],11,10);
                try{
                    $data2 = \DateTime::createFromFormat('Y-m-d', $str2);
                    if($data2 && $data2->format('Y-m-d') === $str2){
                       $turmas = $turmas->where('data_termino','<=',$data2);
                    }
                }
                catch(\Exception $e){
                    unset($filtros['pordata']);
                }
            }
            else{
                try{
                    $data1 = \DateTime::createFromFormat('Y-m-d', $str1);
                    if($data1 && $data1->format('Y-m-d') === $str1){
                       $turmas = $turmas->where('data_inicio','>=',$data1);
                    }
                    

                }
                catch(\Exception $e){
                    unset($filtros['pordata']);
                }
                $str2 = substr($filtros['pordata'][0],12,10);
                try{
                    $data2 = \DateTime::createFromFormat('Y-m-d', $str2);
                    if($data2 && $data2->format('Y-m-d') === $str2){
                       $turmas = $turmas->where('data_termino','<=',$data2);
                    }
                }
                catch(\Exception $e){
                    unset($filtros['pordata']);
                }
            }

        }

        if(isset($filtros['periodo']) && count($filtros['periodo'])){
            if(count($filtros['periodo'])==1){
                $elemento = current($filtros['periodo']);
                $intervalo = \App\classes\Data::periodoSemestreTurmas($elemento);
                $turmas = $turmas->whereBetween('turmas.data_inicio', $intervalo);
            }      
            else{
                //Parameter Grouping
                $turmas = $turmas->where(function ($query) use ($filtros){
                    foreach($filtros['periodo'] as $periodo){
                        $intervalo = \App\classes\Data::periodoSemestreTurmas($periodo);
                        $query = $query->orWhereBetween('turmas.data_inicio', $intervalo);
                    }

                });
            }
               
        }
        if(!isset($filtros['periodo']) && !isset($filtros['status']) && !isset($filtros['status_matriculas'])){
            $turmas = $turmas->whereIn('turmas.status', ['iniciada','lancada']); 

        }         

        $turmas = $turmas->orderBy('cursos.nome')->orderBy('disciplinas.nome');

        $turmas = $turmas->paginate($ipp);

        foreach($turmas as $turma){
            //$turma->parcelas = Turma::find($turma->id);
            $pacote = TurmaDados::where('dado','pacote')->where('turma',$turma->id)->first();
            $turma->pacote = $pacote;
            $turma->parcelas = $turma->getParcelas();
            $turma->sala = $turma->getSala();
        }
        
        return $turmas;

    }




    /**
     * Listagem de turmas da secretaria
     * 
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function listarSecretaria(Request $request)
    {
        
        $turmas = $this->listagemGlobal($request->filtro,$request->valor,$request->removefiltro,$request->remove);
        $programas=Programa::all();
        $professores=PessoaDadosAdministrativos::getEducadores();
        $locais = Local::select(['id','sigla','nome'])->orderBy('sigla')->get();

        
        return view('turmas.listar-secretaria', compact('turmas'))->with('programas',$programas)->with('professores', $professores)->with('locais',$locais)->with('filtros',session()->get('filtro_turmas'))->with('periodos',\App\classes\Data::semestres());
    }


       /**
    * Lista em Brnco
    * @param  [type] $id [description]
    * @return [type]     [description]
    */
    public function impressao($id){
        $inscritos=\App\Models\Inscricao::where('turma',$id)->whereIn('status',['regular','espera','ativa','pendente'])->get();
        $inscritos= $inscritos->sortBy('pessoa.nome');
        if(count($inscritos))
            return view('frequencias.impressao-unitaria',compact('inscritos'))->with('i',1);
        else
            return "Nenhum aluno cadastrado para esta turma.";
    }

    

    /**
     * IMpressão de lista de chamada
     *
     * @param [type] $ids
     * @return void
     */
    public function impressaoMultipla($ids){
        
        $turmas_arr = explode(',',$ids);
        $turmas = \App\Models\Turma::whereIn('id',$turmas_arr)->get();

        
        foreach($turmas as $turma){
            $turma->inscritos =\App\Models\Inscricao::where('turma',$turma->id)->whereIn('status',['regular','espera','ativa','finalizada'])->get();
            $turma->inscritos = $turma->inscritos->sortBy('pessoa.nome');
        }
        //dd($turmas);
        return view('frequencias.impressao-multiplas',compact('turmas'));
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $programas=Programa::get();
        $requisitos=RequisitosController::listar();
        $professores=PessoaDadosAdministrativos::getEducadores();
        $unidades=Local::get(['id' ,'nome']);
        $parcerias=Parceria::orderBy('nome')->get();
        $pacote_cursos =\App\Models\PacoteCurso::get();
        $dados=collect();
        $dados->put('programas',$programas);
        $dados->put('professores',$professores);
        $dados->put('unidades',$unidades);
        $dados->put('parcerias',$parcerias);

        //return $dados;

        return view('turmas.cadastrar',compact('dados'))->with('requisitos',$requisitos)->with('pacote_cursos',$pacote_cursos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            "programa"=>"required|numeric",
            "curso"=>"required|numeric",
            "professor"=>"required|numeric",
            "unidade"=>"required|numeric",
            "dias"=>"required",
            "dt_inicio"=>"date|required",
            "dt_termino"=>"date|required",
            "hr_inicio"=>"required",
            "hr_termino"=>"required",
            "vagas"=>"numeric|required",
            "sala"=>"numeric",
            "valor"=>"required",
            "parcelas"=>"required|numeric"


        ]);

        if(date('Y', strtotime($request->dt_inicio))<date('Y')){
            return redirect()->back()->withErrors(['Não é possível cadastrar turmas de anos anteriores']);
            
        }
        
        //verificar disponibilidade da sala **************************************************************************************


        $turma=new Turma;
        $turma->programa=$request->programa;
        $turma->curso=$request->curso;
        $turma->disciplina=$request->disciplina;
        $turma->professor=$request->professor;
        $turma->local=$request->unidade;
        $turma->dias_semana=$request->dias;
        $turma->data_inicio=$request->dt_inicio;
        $turma->data_termino=$request->dt_termino;
        $turma->hora_inicio=$request->hr_inicio;
        $turma->hora_termino=$request->hr_termino;
        $turma->parceria=$request->parceria;
        $turma->periodicidade=$request->periodicidade;
        $turma->valor=str_replace(',','.',str_replace('.','',$request->valor));
        $turma->parcelas = $request->parcelas;
        $turma->vagas=$request->vagas;
        $turma->carga=$request->carga;
        $turma->sala=$request->sala;
        $turma->idade_min = $request->idade_min;
        $turma->idade_max = $request->idade_max;
        $turma->atributos=$request->atributo;
        $turma->status='lancada';
        $turma->save();

        if(isset($request->requisito)){
            foreach($request->requisito as $req){
                $curso_requisito=new CursoRequisito;
                $curso_requisito->para_tipo='turma';
                $curso_requisito->curso=$turma->id;
                $curso_requisito->requisito=$req;
                $curso_requisito->obrigatorio=1;
                $curso_requisito->timestamps=false;
                $curso_requisito->save();
            }
        }

        if(isset($request->pacote) && is_array($request->pacote) && count($request->pacote)>0){
            foreach($request->pacote as $pcte) {
                $pacote = new \App\Models\TurmaDados;
                $pacote->turma = $turma->id;
                $pacote->dado = 'pacote';
                $pacote->valor = $pcte;
                $pacote->save();
            }

        }

        if(isset($request->mista)){
            $dado_turma = new \App\Models\TurmaDados;
            $dado_turma->turma = $turma->id;
            $dado_turma->dado = 'mista_emg';
            $dado_turma->valor = '1';
            $dado_turma->save();
            unset($dado_turma);

        }

        if(isset($request->vagas_emg)){
            $dado_turma = new \App\Models\TurmaDados;
            $dado_turma->turma = $turma->id;
            $dado_turma->dado = 'vagas_emg';
            $dado_turma->valor = $request->vagas_emg;
            $dado_turma->save();
            unset($dado_turma);
        }
    
        if(isset($request->professor_extra)){
            $dado_turma = new \App\Models\TurmaDados;
            $dado_turma->turma = $turma->id;
            $dado_turma->dado = 'professor_extra';
            $dado_turma->valor = $request->professor_extra;
            $dado_turma->save();
            unset($dado_turma);
        }

        if(isset($request->proxima_turma)){
            $dado_turma = new \App\Models\TurmaDados;
            $dado_turma->turma = $turma->id;
            $dado_turma->dado = 'proxima_turma';
            $dado_turma->valor = $request->proxima_turma;
            $dado_turma->save();
            unset($dado_turma);
        }
        TurmaLogController::registrar('turma',$turma->id,'Turma cadastrada', \Auth::user()->pessoa);

        if($request->btn==1)
            return redirect(asset('/turmas'));
        if($request->btn==2)
            return redirect(asset('/turmas/cadastrar'));
        if($request->btn==3){
            //alterar status da ficha
            $ficha = \App\Models\FichaTecnica::find($request->ficha);
            $ficha->status = 'lancada';
            $ficha->turma = $turma->id;
            $ficha->save();

            //criar um item que aponte para a turma gerada
            return redirect(asset('/turmas/gerar-por-ficha/'))->with('success','Turma '.$turma->id. ' cadastrada com sucesso.');
        }
            
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Turma  $turma
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $turma=Turma::find($id);
        if($turma){
            

            //limita edição somente até marco do ano seguinte
            if((date('Y')-substr($turma->data_inicio,6,4))>=1){   
                if((date('Y')-substr($turma->data_inicio,6,4))!=1 || date('md') > self::DATA_LIMITE_ALTERACAO)
                    return redirect()->back()->withErrors(['Não é possível modificar dados de turmas de anos anteriores.']);    
            }

            $programas=Programa::orderBy('nome')->get();
            $parcerias=Parceria::orderBy('nome')->get();
            $requisitos=RequisitosController::listar();
            $professores=PessoaDadosAdministrativos::getEducadores();
            $unidades=Local::orderBy('nome')->get();
            $salas= \App\Models\Sala::where('local',$turma->local->id)->get();
            $dados=collect();
            $dados->put('programas',$programas);
            $dados->put('professores',$professores);
            $dados->put('unidades',$unidades);
            $dados->put('parcerias', $parcerias);
            $dados->put('salas',$salas);
            $pacote_cursos =\App\Models\PacoteCurso::get();
            $turma->data_iniciov=Data::converteParaBd($turma->data_inicio);
            $turma->data_terminov=Data::converteParaBd($turma->data_termino);
            $turma_dados = \App\Models\TurmaDados::where('turma',$turma->id)->get();
            $turma->pacote = $turma_dados->where('dado','pacote')->pluck('valor')->toArray();
            $turma->mista = $turma_dados->where('dado','mista_emg')->first();
            $turma->vagas_emg = $turma_dados->where('dado','vagas_emg')->first();
            $turma->proxima = $turma_dados->where('dado','proxima_turma')->pluck('valor')->toArray();
            $turma->professor_extra = $turma_dados->where('dado','professor_extra')->pluck('valor')->first();
            $turma->professor_substituto = $turma_dados->where('dado','professor_substituto')->pluck('valor')->first();
            $turma->chamada_liberada = $turma_dados->where('dado','chamada_liberada')->first();

            

            
            
           
            foreach($requisitos as $requisito){
                $rc=CursoRequisito::where('curso', $turma->id)->where('para_tipo','turma')->where('requisito',$requisito->id)->first();
                if(isset($rc->id)){
                    $requisito->checked="checked";
                    if($rc->obrigatorio==1)
                        $requisito->obrigatorio="checked";
                }
            }
            
            //$turma->vagas_emg = $turma_dados->where('dado','vagas_emg')->pluck('valor')->first();

            return view('turmas.editar',compact('dados'))->with('turma',$turma)->with('requisitos',$requisitos)->with('pacote_cursos',$pacote_cursos);
        }
        else
            return $this->index();
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Turma  $turma
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Turma $turma)
    {
        $this->validate($request, [
            "turmaid"=>"required|numeric",
            "programa"=>"required|numeric",
            "curso"=>"required|numeric",
            "professor"=>"required|numeric", 
            "unidade"=>"required|numeric",
            "dias"=>"required",
            "dt_inicio"=>"required",
            "hr_inicio"=>"required",
            "vagas"=>"required",
            "valor"=>"required",
            "sala"=>"numeric",
            "parcelas"=>"required:numeric"


        ]);
        
        //limita edição somente até marco do ano seguinte
        if(date('Y')-date('Y', strtotime($request->dt_inicio))>=1){ 
            
            if(date('Y')-date('Y', strtotime($request->dt_inicio))==1 && date('md') < self::DATA_LIMITE_ALTERACAO)
            $alteracoes = 'Edição dos dados: '; 
            else
                return redirect()->back()->withErrors(['Não é possível modificar dados de turmas de anos anteriores.']);
            
        }
      
        
        
        //verificar disponibilidade da sala.
        //verificar se a turma tem aulas executadas e matriculas ativas

        $turma=Turma::find($request->turmaid);

        
        if($turma->status == 'iniciada'){
            
            if($turma->matriculados >0 && \Carbon\Carbon::createFromFormat('d/m/Y', $turma->data_inicio)->format('Y-m-d')!=$request->dt_inicio)               
                return redirect()->back()->withErrors(['Não é possível alterar datas de início de turmas com alunos matriculados.']);
        }

        if($turma->data_inicio!=$request->dt_inicio || $turma->dias_semana!=$request->dias ||  $turma->data_termino!=$request->dt_termino ){
            $aula_controller = new AulaController;
            $aula_controller->recriarAulas($turma->id);
            
        }


        $turma->programa=$request->programa;
        $turma->curso=$request->curso;
        $turma->disciplina=$request->disciplina;
        $turma->professor=$request->professor;
        $turma->local=$request->unidade;
        $turma->dias_semana=$request->dias;
        $turma->data_inicio=$request->dt_inicio;
        $turma->data_termino=$request->dt_termino;
        $turma->hora_inicio=$request->hr_inicio;
        $turma->hora_termino=$request->hr_termino;
        $turma->valor=str_replace(',','.',str_replace('.','',$request->valor));
        $turma->parcelas = $request->parcelas;
        $turma->vagas=$request->vagas;
        $turma->carga=$request->carga;
        $turma->atributos=$request->atributo;
        $turma->periodicidade=$request->periodicidade;
        $turma->parceria = $request->parceria;
        $turma->sala=$request->sala;
        $turma->idade_min = $request->idade_min;
        $turma->idade_max = $request->idade_max;

        

        

        $alteracoes = 'Edição dos dados: ';
        $colunas = \Schema::getColumnListing('turmas'); // users table        
        foreach($colunas as $dado){
            if($turma->isDirty($dado))
                if(isset($turma->$dado->id) && isset(($turma->getOriginal($dado))->id))
                    $alteracoes.= $dado.' alterado '. $turma->$dado->id . ' => '.($turma->getOriginal($dado))->id.', ';
                else{
                    if(is_array($turma->$dado))
                        $alteracoes.= $dado.' alterado '. implode(',',$turma->$dado) . ' => '.implode(', ',$turma->getOriginal($dado));
                    else
                        $alteracoes.= $dado.' alterado '. $turma->$dado . ' => '.$turma->getOriginal($dado).', ';
                }

        }
        
        TurmaLogController::registrar('turma',$turma->id,$alteracoes, \Auth::user()->pessoa);
        $turma->update();

        $turma_dados = \App\Models\TurmaDados::where('turma',$turma->id)->get();
       
        $turma->proxima = $turma_dados->where('dado','proxima_turma');
        foreach($turma->proxima as $nx_turma){
            \App\Models\TurmaDados::destroy($nx_turma->id);
            if($request->proxima_turma !='')
                TurmaLogController::registrar('turma',$turma->id,'Próxima turma excluída', \Auth::user()->pessoa);

        }
        if($request->proxima_turma !=''){   
            $nx_turma = new \App\Models\TurmaDados;
            $nx_turma->turma = $turma->id;
            $nx_turma->dado = 'proxima_turma';
            $nx_turma->valor = $request->proxima_turma;
            $nx_turma->save();
            TurmaLogController::registrar('turma',$turma->id,'Modificação de proxima turma ', \Auth::user()->pessoa);

        }
        
        //Atributos de pacotes de valores
        $turma->pacote = $turma_dados->where('dado','pacote')->pluck('valor')->toArray();
        foreach($turma->pacote as $pcte){ 
            if(!isset($request->pacote) || !in_array($pcte,$request->pacote)){
                \App\Models\TurmaDados::where('turma',$turma->id)->where('dado','pacote')->where('valor',$pcte)->delete();
                TurmaLogController::registrar('turma',$turma->id,'Remoção do pacote '.$pcte, \Auth::user()->pessoa);

            }
        }
        if(isset($request->pacote)){
            foreach($request->pacote as $pcte){
                if(!in_array($pcte,$turma->pacote)){
                    $pacote = new \App\Models\TurmaDados;
                    $pacote->turma = $turma->id;
                    $pacote->dado = 'pacote';
                    $pacote->valor = $pcte;
                    $pacote->save();
                    TurmaLogController::registrar('turma',$turma->id,'Adição do pacote '.$pcte, \Auth::user()->pessoa);
                }
                    
            }
        }
        
        //Atributo de turma mista EMG
        $turma->mista = $turma_dados->where('dado','mista_emg');
        foreach($turma->mista as $mista){
            \App\Models\TurmaDados::destroy($mista->id);
            if(!isset($request->mista))
                TurmaLogController::registrar('turma',$turma->id,'Removido atributo de turma mista EMG ', \Auth::user()->pessoa);

        }
        if(isset($request->mista)){   
            $dado_mista = new \App\Models\TurmaDados;
            $dado_mista->turma = $turma->id;
            $dado_mista->dado = 'mista_emg';
            $dado_mista->valor = '1';
            $dado_mista->save();
            TurmaLogController::registrar('turma',$turma->id,'Adicionado atributo de turma mista EMG ', \Auth::user()->pessoa);
        }
    
        //Atributo de vagas EMG
        $turma->vagas_emg = $turma_dados->where('dado','vagas_emg');
        foreach($turma->vagas_emg as $vagas_emg){
            \App\Models\TurmaDados::destroy($vagas_emg->id);
            if(!isset($request->vagas_emg))
                TurmaLogController::registrar('turma',$turma->id,'Removido atributo vagas EMG ', \Auth::user()->pessoa);
        }
        if(isset($request->vagas_emg)){   
            $dado_vagas_emg = new \App\Models\TurmaDados;
            $dado_vagas_emg->turma = $turma->id;
            $dado_vagas_emg->dado = 'vagas_emg';
            $dado_vagas_emg->valor = $request->vagas_emg;
            $dado_vagas_emg->save();
            TurmaLogController::registrar('turma',$turma->id,'Adicionado atributo de vagas EMG ', \Auth::user()->pessoa);
        }

        //Atributo de professor extra
        $turma->prof_extra = $turma_dados->where('dado','professor_extra');
        foreach($turma->prof_extra as $prof_extra){
            \App\Models\TurmaDados::destroy($prof_extra->id);
            if($request->professor_extra == 0)
                TurmaLogController::registrar('turma',$turma->id,'Removido atributo professor extra', \Auth::user()->pessoa);
        }
        if($request->professor_extra>0){   
            $prof_extra = new \App\Models\TurmaDados;
            $prof_extra->turma = $turma->id;
            $prof_extra->dado = 'professor_extra';
            $prof_extra->valor = $request->professor_extra;
            $prof_extra->save();
            TurmaLogController::registrar('turma',$turma->id,'Adicionado atributo de professor extra', \Auth::user()->pessoa);
        }

        //Atributo de professor substituto
        $prof_substituto = \App\Models\TurmaDados::where('turma',$turma->id)->where('dado','professor_substituto')->first();
        if($prof_substituto){
            if($request->professor_substituto == 0){
                \App\Models\TurmaDados::destroy($prof_substituto->id);
                TurmaLogController::registrar('turma',$turma->id,'Professor substituto removido');
            }
            else{
                if($prof_substituto->valor != $request->professor_substituto){
                    $prof_substituto->valor = $request->professor_substituto;
                    $prof_substituto->save();
                    TurmaLogController::registrar('turma',$turma->id,'Professor substituto modificado');
                }
            }
        }
        else{
            if($request->professor_substituto > 0){
                $prof_substituto = new \App\Models\TurmaDados;
                $prof_substituto->turma = $turma->id;
                $prof_substituto->dado = 'professor_substituto';
                $prof_substituto->valor = $request->professor_substituto;
                $prof_substituto->save();
                TurmaLogController::registrar('turma',$turma->id,'Professor substituto adicionado');
            }
                
        }

        //Atributo de chamada liberada
        $chamada_liberada = \App\Models\TurmaDados::where('turma',$turma->id)->where('dado','chamada_liberada')->first();
        if($request->chamada_liberada){
            if(!isset($chamada_liberada->id)){
                $chamada_liberada = new \App\Models\TurmaDados;
                $chamada_liberada->turma = $turma->id;
                $chamada_liberada->dado = 'chamada_liberada';
                $chamada_liberada->valor = true;
                $chamada_liberada->save();
                TurmaLogController::registrar('turma',$turma->id,'Chamada liberada foi ativada');
            }
        }
        else{
            if($chamada_liberada){
                \App\Models\TurmaDados::destroy($chamada_liberada->id);
                TurmaLogController::registrar('turma',$turma->id,'Chamada liberada foi desativada');
            }
        }
       
        //Atributo de requisitos
        CursoRequisito::where('curso', $turma->id)->where('para_tipo','turma')->delete();
        if($request->requisito){
            foreach($request->requisito as $requisito){
                $reqcur = new CursoRequisito;
                $reqcur->para_tipo = 'turma'; 
                $reqcur->curso = $turma->id;
                $reqcur->requisito = $requisito;
                $reqcur->timestamps=false;

                if(isset($request->obrigatorio))
                    if(in_array($requisito, $request->obrigatorio))
                        $reqcur->obrigatorio = 1;       
                    else
                        $reqcur->obrigatorio = 0;
                else
                    $reqcur->obrigatorio = 0;

                $reqcur->save();

            
            }
        }
        return redirect(asset('/turmas'))->with(['success'=>'Turma modificada com sucesso.']);   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Turma  $turma
     * @return \Illuminate\Http\Response
     */
    public function destroy($itens_url)
    {
        $msgs=Array();
        $turmas=explode(',',$itens_url);
        foreach($turmas as $turma){
            if(is_numeric($turma)){
                $turma=Turma::find($turma);
                if($turma){
                    $inscricoes=Inscricao::where('turma',$turma->id)->count();
                    if($inscricoes==0){
                        $msgs[]= "Turma ".$turma->id." excluída com sucesso.";
                        TurmaLogController::registrar('turma',$turma->id,'Turma excluída.', \Auth::user()->pessoa);
                        $turma->delete();
                    }
                    else{
                        TurmaLogController::registrar('turma',$turma->id,'Solicitação de exclusão de turma falha por já conter inscrições', \Auth::user()->pessoa);
                        $msgs[]="Turma ".$turma->id." não pôde ser excluída pois possui alunos inscritos. Caso não apareça, a inscrição pode ter sido cancelada, mesmo assim precisamos preservar o histórico das inscrições.";
                    }
                }
            }
        }
        return redirect()->back()->withErrors($msgs);
    }



    public function status($status,$itens_url)
    {
        $turmas=explode(',',$itens_url);
        foreach($turmas as $turma){
            if(is_numeric($turma)){
                $turma=Turma::find($turma);
                if(isset($turma->id)){
                    TurmaLogController::registrar('turma',$turma->id,'Status alterado de '.$turma->status.' para '.$status, \Auth::user()->pessoa);  
                    switch($status){
                        case 'encerrada':
                            $this->finalizarTurma($turma);
                            break;
                        case 'iniciada' : //turmas abertas que aceitam matriculas
                            //pegar todas inscriçoes finalizadas e espera da turma e colocar como regular
                            $lista_inscricoes = '';
                            $inscricoes = $turma->getInscricoes(['finalizada','espera']);                            
                            foreach($inscricoes as $inscricao){
                                $lista_inscricoes .= $inscricao->id.',';
                            }
                            InscricaoController::alterarStatus($lista_inscricoes,'regular');
                            unset($turma->inscricoes);
                            break;
                        case 'cancelada': 
                            $turma->status_matriculas = 'fechada';
                            
                            break;
                        default:
                            $turma->status=$status;
                            break;
                            

                    }
                    $turma->status=$status;
                    $turma->save();
                    
                    
                    
                }
            }
        }
        return redirect()->back()->with('success','Status das turmas alteradas com sucesso.');
    }

    public function statusMatriculas($status,$itens_url)
    {
        $turmas=explode(',',$itens_url);
        foreach($turmas as $turma){
            if(is_numeric($turma)){
                $turma=Turma::find($turma);
                TurmaLogController::registrar('turma',$turma->id,'Status de Matrículas da turma alterado de '.$turma->status_matriculas.' para '.$status, \Auth::user()->pessoa);
                if(isset($turma->id) && $turma->status!='encerrada' && $turma->status!='cancelada'){
                    $turma->status_matriculas = $status;
                    $msgs['alert_sucess'][]="Turma ".$turma->id." modificada com sucesso.";      
                    $turma->save();
                    
                }
                else
                    $msgs['alert_warning'][]="Turma ".$turma->id." não pôde ser alteradada pois está encerrada ou cancelada.";   
            }
        }
        return redirect()->back()->withErrors($msgs);
    }


    public function turmasDisponiveis($pessoa, $turmas_atuais='0',$query='')
    {
        
        $turmas_af=collect();
        $turmas_conflitantes=array();
        $dias = array();

        //transforma a string de turmas atuais em collection de turmas
        $turmas_atuais=explode(',',$turmas_atuais);
        foreach($turmas_atuais as $turma){
            if(is_numeric($turma)){
                if(Turma::find($turma))
                    $turmas_af->push(Turma::find($turma));
            }
        }
        //se não tiver nenhuma turma atual
        if(count($turmas_af)==0){ 

            $turmas= Turma::select(['turmas.*','cursos.nome as nome_curso','disciplinas.nome as disciplina_nome','pessoas.nome as nome_professor','programas.sigla as sigla_programa'])
                ->join('cursos', 'cursos.id', '=', 'turmas.curso')
                ->leftjoin('disciplinas', 'disciplinas.id', '=', 'turmas.disciplina')
                ->join('pessoas', 'pessoas.id', '=', 'turmas.professor')
                ->join('programas', 'programas.id', '=', 'turmas.programa')
                ->whereIn('turmas.status_matriculas', ['aberta','presencial'])
                ->where(function($busca) use ($query){
                    $busca->where('cursos.nome', 'like','%'.$query.'%')
                            ->orwhere('turmas.id', $query)
                            ->orwhere('disciplinas.nome', 'like','%'.$query.'%')
                            ->orwhere('pessoas.nome', 'like','%'.$query.'%')
                            ->orwhere('programas.sigla', 'like','%'.$query.'%')
                            ->orwhere('dias_semana', 'like','%'.$query.'%');
                })
                ->orderBy('cursos.nome')->orderBy('disciplinas.nome')
                ->limit(30)
                ->get();

                //$turmas = Turma::whereIn('id',$turmas_query)->get();

                foreach($turmas as $turma){
                    //$turma->parcelas = Turma::find($turma->id);
                    $pacote = TurmaDados::where('dado','pacote')->where('turma',$turma->id)->first();
                    $turma->pacote = $pacote;
                    $turma->parcelas = $turma->getParcelas();
                    
                }

                
           
            return view('turmas.lista-matricula', compact('turmas'))->with('pessoa',$pessoa);
        }
        //cria limitação nos horários das turmas
        else{
            foreach($turmas_af as $turma){
                    $hora_fim=date("H:i",strtotime($turma->hora_termino." - 1 minutes"));
                    /*if($turma->id == 3069){
                        dd($hora_fim,$turma->hora_termino);
                    }*/
                    foreach($turma->dias_semana as $turm){
                        $data = \Carbon\Carbon::createFromFormat('d/m/Y', $turma->data_termino)->format('Y-m-d');
                        //listar turmas que tenham conflito de horário

                        $turmas_conflitantes = array_merge($turmas_conflitantes,
                        /*Turma::where('dias_semana', 'like', '%'.$turm.'%')
                            ->where(function($t) use ($turma,$hora_fim){
                                $t->whereBetween('hora_inicio', [$turma->hora_inicio,$hora_fim])
                                    ->orWhere('hora_termino', '>=',$hora_fim) ;  
                            })                         
                            ->where('data_inicio','<=',$data)
                            ->whereIn('status',['iniciada','espera'])
                            ->pluck('id')->toArray()); */
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
                        $dias[]=$turm;

                    }
                    //dd($turmas_conflitantes,[$turma->hora_inicio,$hora_fim,$data,$dias]);
                    
                }   
          
            $turmas = Turma::select(['turmas.*','cursos.nome as nome_curso','disciplinas.nome as disciplina_nome','pessoas.nome as nome_professor','programas.sigla as sigla_programa'])
                ->join('cursos', 'cursos.id', '=', 'turmas.curso')
                ->leftjoin('disciplinas', 'disciplinas.id', '=', 'turmas.disciplina')
                ->join('pessoas', 'pessoas.id', '=', 'turmas.professor')
                ->join('programas', 'programas.id', '=', 'turmas.programa')
                ->whereIn('turmas.status_matriculas', ['aberta','presencial'])
                ->whereNotIn('turmas.id', array_unique($turmas_conflitantes))
                ->where(function($busca) use ($query){
                    $busca->where('cursos.nome', 'like','%'.$query.'%')
                            ->orwhere('turmas.id',$query)
                            ->orwhere('disciplinas.nome', 'like','%'.$query.'%')
                            ->orwhere('pessoas.nome', 'like','%'.$query.'%')
                            ->orwhere('programas.sigla', 'like','%'.$query.'%')
                            ->orwhere('dias_semana', 'like','%'.$query.'%');
                })
                ->orderBy('cursos.nome')->orderBy('disciplinas.nome')
                ->get();
            foreach($turmas as $turma){
                $turma->parcelas = $turma->getParcelas();
                $pacote = TurmaDados::where('dado','pacote')->where('turma',$turma->id)->first();
                $turma->pacote = $pacote;
                //$turma->nome_curso = $turma->getNomeCurso();
            }
        }
        return view('turmas.lista-matricula', compact('turmas'))->with('pessoa',$pessoa);  
    }


    public function turmasEscolhidas($lista='0'){
        $turmas=collect();
        $valor=0;
        $uati=0;
        $lista=explode(',',$lista);
        foreach($lista as $turma){
            if(is_numeric($turma)){
                $db_turma=Turma::find($turma);
                if(isset($db_turma->id))
                    $turmas->push($db_turma);
            }

        }


        return view('secretaria.inscricao.turmas-escolhidas', compact('turmas'))->with('valor',$valor);

    }
    public static function csvTurmas($lista='0'){
        $turmas=collect();
        $valor=0;
        $parcelas=4;
        $lista=explode(',',$lista);
        foreach($lista as $turma){
            if(is_numeric($turma)){
                if(Turma::find($turma))
                    $turmas->push(Turma::find($turma));
            }
        }
        return $turmas->sortBy('hora_inicio');

    }

    /*public function turmasJSON(){
        $programas=Programa::get();
        foreach($programas as $programa){
            $turmas=Turma::where('turmas.programa',$programa->id)
                                        ->join('cursos','turmas.curso','=','cursos.id')
                                        ->leftjoin('disciplinas','turmas.disciplina','=','disciplinas.id')
                                        ->get();
            foreach($turmas as $turma){
                $dados[$programa->sigla][$turma->id]=$turmas;
            }
        }
        return $dados;
    }*/

    public function turmasSite(){

        
        $turmas=Turma::whereIn('turmas.status_matriculas', ['aberta','online','presencial'])
                ->whereIn('turmas.local',[84,85,86,118])
                ->whereColumn('turmas.vagas','>','turmas.matriculados')
                ->get();
        foreach($turmas as $turma){
            $turma->nome_curso = $turma->getNomeCurso();
        }
        $turmas = $turmas->sortBy('nome_curso');

        
        return view('turmas.turmas-site',compact('turmas'));
    }

 
    public function turmasProfessor(Request $r){
        $turmas=Turma::whereIn('turmas.status', ['lancada','iniciada'])
                ->whereIn('turmas.local',[84,85,86])
                ->whereColumn('turmas.vagas','>','turmas.matriculados')
                ->where('professor',$r->professor)
                ->get();
        foreach($turmas as $turma){
            $turma->nome_curso = $turma->getNomeCurso();
        }
        $turmas = $turmas->sortBy('nome_curso');
        $professor=Pessoa::find($r->professor);

        return view('turmas.turmas-site',compact('turmas'))->with('professor',$professor->nomeSimples);

    }
    
    public function uploadImportaTurma(Request $request){

        $ext = $request->arquivo->extension();
        //dd($ext);
        if($ext != 'xlsx' && $ext != 'XLSX')
        return redirect()->back()->withErrors('Erro: o arquivo importado precisa ser do tipo excel xlsx (>2003)');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($request->arquivo);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();
        if($highestRow>500)
            return redirect()->back()->withErrors('Erro: o arquivo importado não pode ter mais de 500 registros neste momento.');
        // Verificador da versão do modelo (nesse caso verifica se coluna E = e-mail)
        if($worksheet->getCell('E1')->getValue()!='E-mail')
            return redirect()->back()->withErrors('Erro: Versão do arquivo diferente do modelo. ');
        
        
            $pessoas = collect();
        for($i=2;$i<=$highestRow;$i++){
            if($worksheet->getCell('A'.$i)->getValue() != null){
                $insc= (object)[];
                $insc->id = $i;
                $insc->nome=trim($worksheet->getCell('A'.$i)->getValue());
                $insc->nascimento=trim(iconv("UTF-8","ISO-8859-1",$worksheet->getCell('G'.$i)->getFormattedValue())," \t\n\r\0\x0B\xA0");
                try{
                    $insc->nascimento = \Carbon\Carbon::createFromFormat('m/d/Y', $insc->nascimento)->format('Y-m-d');
                }
                catch(\Exception $e){
                    
                }

                $insc->genero=trim($worksheet->getCell('B'.$i)->getValue());
                $insc->rg=trim($worksheet->getCell('C'.$i)->getValue());
                $insc->cpf=trim($worksheet->getCell('D'.$i)->getValue());
                $insc->email=trim($worksheet->getCell('E'.$i)->getValue());
                $insc->fone=trim($worksheet->getCell('F'.$i)->getValue());         
                $insc->endereco=trim($worksheet->getCell('H'.$i)->getValue());
                $insc->numero=trim($worksheet->getCell('I'.$i)->getValue());
                $insc->bairro=trim($worksheet->getCell('K'.$i)->getValue());
                $insc->complemento=trim($worksheet->getCell('J'.$i)->getValue());
                $insc->cep=trim($worksheet->getCell('L'.$i)->getValue());
                $insc->cidade=trim($worksheet->getCell('M'.$i)->getValue());
                $insc->estado=trim($worksheet->getCell('N'.$i)->getValue());
                $insc->turma=trim($worksheet->getCell('O'.$i)->getValue());
                $pessoas->push($insc);
            }
        }
        $pessoas = $pessoas->sortBy('nome');

        //dd($pessoas);

        return view('turmas.listar-importados')->with('pessoas',$pessoas)->with('arquivo',$request->arquivo);
    }



    
    /**
     * Modifica a quantidade de pessoas inscritas na turma.
     * @param  \App\Models\Turma  $turma
     * @param  $operaçao - 0 reduz, 1 aumenta
     * @param  $qnde - numero para adicionar ou reduzir
     * @return \Illuminate\Http\Response
     */
    public static function modInscritos($turma){
        $turma=Turma::find($turma);
        $inscricoes = Inscricao::where('turma',$turma->id)->whereIn('status',['regular','pendente'])->count();
        $turma->matriculados = $inscricoes;
        $turma->save();
        
    }


    /**
     * acao em lote verifica alguma ação pedagogica nas turmas que receber por post
     */
    public function acaolote($acao, $turmas){
        $turmas = explode(',',$turmas);
        if(count($turmas) == 0)
                     return redirect()->back()->withErrors(['Não foi possivel efetuar sua solicitação: Nenhuma turma selecionada.']);
        switch ($acao) {
            case 'encerrar':
                
                foreach($turmas as $turma_id){
                    $turma = Turma::find($turma_id);
                    if(isset($turma->id)){
                        $this->finalizarTurma($turma);
                        TurmaLogController::registrar('turma',$turma->id,'Turma encerrada', \Auth::user()->pessoa);
                    }
                }
                return redirect()->back()->withErrors(['Turmas encerradas com sucesso']);
                break;
            case 'relancar':
                
                $programas=Programa::orderBy('nome')->get();
                $professores=PessoaDadosAdministrativos::getEducadores();
                $unidades=Local::orderBy('nome')->get(['id' ,'nome']);
                $parcerias=Parceria::orderBy('nome')->get();
                $dados=collect();

                $dados->put('programas',$programas);
                $dados->put('professores',$professores);
                $dados->put('unidades',$unidades);
                $dados->put('parcerias',$parcerias);

                //dd($dados);

                return view('turmas.recadastrar',compact('dados'))->with('turmas',$turmas);
                break;

            case 'requisitos':
                $turmas = implode(',',$turmas);
                $requisitos = \App\Models\Requisito::all();
                return redirect('/turmas/modificar-requisitos/'.$turmas);


                break;
            
            default:
                return redirect()->back()->withErrors(['Não foi possivel identificar sua solicitação.']);
                break;
        }
    }

    
    public function storeRecadastro(Request $r){
        
        
        $turmas = explode(',', $r->turmas);

        
        foreach($turmas as $turma_id){

            if($turma_id>0)
            $turma=Turma::find($turma_id);

            else
                continue;
            if(!isset($turma->id))
                continue;

            $novaturma = new Turma;
            if($r->programa > 0 )
                $novaturma->programa = $r->programa;
            else
                $novaturma->programa = $turma->programa->id;

            if($r->curso > 0 )
                $novaturma->curso = $r->curso;
            else
                $novaturma->curso = $turma->curso->id;

            if($r->disciplina > 0 )
                $novaturma->disciplina = $r->disciplina;
            else
                if(isset( $turma->disciplina))
                    $novaturma->disciplina = $turma->disciplina->id;

            if($r->professor > 0 )
                $novaturma->professor = $r->professor;
            else
                $novaturma->professor = $turma->professor->id;

            if($r->unidade > 0 )
                $novaturma->local = $r->unidade;
            else
                $novaturma->local = $turma->local->id;
            if($r->sala> 0 )
                $novaturma->sala = $r->sala;
            else
                $novaturma->sala = $turma->sala;

            if($r->parceria > 0 )
                $novaturma->parceria = $r->parceria;
            else
                $novaturma->parceria = $turma->parceria;

            if($r->periodicidade != $turma->periodicidade )
                $novaturma->periodicidade = $r->periodicidade;
            else
                $novaturma->periodicidade  = $turma->periodicidade ;

            if(!empty($r->dias))
                $novaturma->dias_semana = $r->dias;
            else
                $novaturma->dias_semana = $turma->dias_semana;

            if($r->dt_inicio != '' )
                $novaturma->data_inicio = $r->dt_inicio;
            else
                $novaturma->data_inicio = \Carbon\Carbon::createFromFormat('d/m/Y', $turma->data_inicio, 'Europe/London')->format('Y-m-d');

            if($r->hr_inicio != '' )
                $novaturma->hora_inicio = $r->hr_inicio;
            else
                $novaturma->hora_inicio = $turma->hora_inicio;

            if($r->dt_termino != '' )
                $novaturma->data_termino = $r->dt_termino;
            else
                $novaturma->data_termino = \Carbon\Carbon::createFromFormat('d/m/Y', $turma->data_termino, 'Europe/London')->format('Y-m-d');

            if($r->hr_termino != '' )
                $novaturma->hora_termino = $r->hr_termino;
            else
                $novaturma->hora_termino = $turma->hora_termino;

            if($r->vagas != '' )
                $novaturma->vagas = $r->vagas;
            else
                $novaturma->vagas = $turma->vagas;

            if($r->valor != '' )
                $novaturma->valor = $r->valor;
            else
                $novaturma->valor = $turma->valor;

            if($r->parcelas != '' )
                $novaturma->parcelas = $r->parcelas;
            else
                $novaturma->parcelas = $turma->parcelas;

            if($r->carga != '' )
                $novaturma->carga = $r->carga;
            else
                $novaturma->carga = $turma->carga;

            $novaturma->status = 'lancada';
            $novaturma->status_matriculas = 'fechada';

            //dd($novaturma);

            $novaturma->save();
            TurmaLogController::registrar('turma',$novaturma->id,'Turma criada a partir da turma '.$turma->id, \Auth::user()->pessoa);
            TurmaLogController::registrar('turma',$turma->id,'Turma relançada sobre o código '.$novaturma->id, \Auth::user()->pessoa);

            $requisitos = CursoRequisito::where('para_tipo','turma')->where('curso',$turma->id)->get();
            foreach($requisitos as $requisito){
                $novo_requisito = new CursoRequisito;
                $novo_requisito->para_tipo = 'turma';
                $novo_requisito->curso = $novaturma->id;
                $novo_requisito->requisito = $requisito->requisito->id;
                $novo_requisito->obrigatorio = $requisito->obrigatorio;
                $novo_requisito->save();
            }
            $dados = TurmaDados::where('turma',$turma->id)->where('dado','<>','proxima_turma')->get();
            foreach($dados as $dado){
                $novo_dado = new TurmaDados;
                $novo_dado->turma = $novaturma->id;
                $novo_dado->dado = $dado->dado;
                $novo_dado->valor = $dado->valor;
                $novo_dado->save();
            }


        }
        
        return redirect('/turmas')->withErrors(['Turmas recadastradas com sucesso.']);
    }
    public static function listarTurmasDocente($docente,$semestre){
        $substituto = \App\Models\TurmaDados::where('dado','professor_substituto')->where('valor',$docente)->pluck('turma')->toArray();
     

        $turmas_subistituir = Turma::whereIn('id',$substituto)->get();

        if($semestre > 0){
            $intervalo = \App\classes\Data::periodoSemestreTurmas($semestre);
            $turmas = Turma::where('professor', $docente)->whereIn('status',['lancada','iniciada','encerrada'])->whereBetween('data_inicio', $intervalo)->orderBy('hora_inicio')->get();
        }
        else{
            $turmas = Turma::where('professor', $docente)->whereIn('status',['lançada','iniciada'])->orderBy('hora_inicio')->get();
        }

        foreach($turmas as $turma){
              
            $turma->weekday = \App\Utils\WeekHandler::toNumber($turma->dias_semana[0]);
            $turma->chamada_regular = \App\Models\Frequencia::verificarPontualidadeChamada($turma->id);

        }
    
        $turmas = $turmas->sortBy('weekday');

        //dd($turmas_subistituir);
        

        //dd($turmas);
         return $turmas->merge($turmas_subistituir);
    }
    public function getChamada($turma_id,$page,$opt,$mostrar='todos'){

        if($opt=='pdf')
            $opt='pdf&rel_pdf=rel_freq';

        if($page == 0)
            $url = "https://script.google.com/macros/s/AKfycbwY09oq3lCeWL3vHoxdXmocjVPnCEeZMVQgzhgl-J0WNOQPzQc/exec?id=".$turma_id."&portrait=false&tipo=".$opt;
        
        else
            $url = "https://script.google.com/macros/s/AKfycbwY09oq3lCeWL3vHoxdXmocjVPnCEeZMVQgzhgl-J0WNOQPzQc/exec?id=".$turma_id."&portrait=false&tipo=".$opt."&page=".$page;

        if($mostrar != 'todos')
            $url .= '&hide=s';
        print '<div style="font-family:tahoma;font-size:10px;margin:50px auto 0;">Acessando sua lista em: '.$url.'</div><br>';

        $ch = curl_init();
        //não exibir cabeçalho
        curl_setopt($ch, CURLOPT_HEADER, false);
        // redirecionar se hover
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // desabilita ssl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //envia a url
        curl_setopt($ch, CURLOPT_URL, $url);
        //executa o curl
        $result = curl_exec($ch);
        //encerra o curl
        curl_close($ch);



       $ws = json_decode($result);


       if( isset($ws[0]->url) )
            return redirect( $ws[0]->url);

        elseif(isset($ws[0]->url_pdf))
            return redirect( $ws[0]->url_pdf);

        else

            return $result;
    }





    public function getPlano($professor,$tipo,$curso){

        print 'Carregando...';
      
        $url = "https://script.google.com/macros/s/AKfycbwY09oq3lCeWL3vHoxdXmocjVPnCEeZMVQgzhgl-J0WNOQPzQc/exec?id_pro=".$professor. "&id_curso=".$curso."&tipo=plano";
        
        $ch = curl_init();
        //não exibir cabeçalho
        curl_setopt($ch, CURLOPT_HEADER, false);
        // redirecionar se hover
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // desabilita ssl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //envia a url
        curl_setopt($ch, CURLOPT_URL, $url);
        //executa o curl
        $result = curl_exec($ch);
        //encerra o curl
        curl_close($ch);

        $ws = json_decode($result);

        if( isset($ws[0]->url) )

            return redirect( $ws[0]->url);

        else

            return $result;
    }



    /**
     * Finaliza turma individualmente
     * @param  [Turma] $turma [Objeto Turma]
     * @return [type]        [description]
     */
    public function finalizarTurma(Turma $turma){
        if($turma->termino > date('Y-m-d'))
            die('Turma '.$turma->id. ' não pode ser encerrada pois a data de término não foi atingida. Se a turma não ocorreu utilize a opção CANCELAR.');
        $inscricoes = Inscricao::where('turma', $turma->id)->get();   
        $turma->status_matriculas = 'fechada';
        $turma->status = 'encerrada';
        $turma->save();

        foreach($inscricoes as $inscricao){

            $executar = InscricaoController::finaliza($inscricao);

        }

        return $turma;

    }

    public function processarTurmasExpiradas(){

        $turmas_finalizadas = 0;
        $turmas = Turma::where('data_termino','<', date('Y-m-d'))
            ->where('status','iniciada')
            ->get();

        foreach($turmas as $turma){

            $this->finalizarTurma($turma);
            $turmas_finalizadas ++;

        }

        return redirect($_SERVER['HTTP_REFERER'])->withErrors([$turmas_finalizadas.' turmas estavam expiradas e foram finalizadas.']);
    }


    public function atualizarInscritos(){
        $turmas = Turma::select(['id','matriculados'])->whereIn('status',['iniciada','lancada'])->get();
        foreach($turmas as $turma){
            $inscritos = Inscricao::where('turma',$turma->id)->whereIn('status',['regular','pendente','finalizada','finalizado'])->count();
            if($turma->matriculados != $inscritos){
                $turma->matriculados = $inscritos;
                $turma->save();
            }

        }
        return "Turmas atualizadas.";
    }

    public function frequencia(int $turma){
        $aulas = \App\Models\Aula::where('turma',$turma)->get();
        if(count($aulas) == 0)
            dd("sem aulas cadastradas pra essa turma. Cadastrar?");
        $frequencias = \App\Models\Frequencia::whereIn('aula',$aulas);
        $inscritos=\App\Models\Inscricao::where('turma',$turma)->whereIn('status',['regular','espera','ativa','pendente'])->get();
        $inscritos= $inscritos->sortBy('pessoa.nome');
        if(count($inscritos))
            return view('frequencias.impressao-unitaria',compact('inscritos'))->with('i',1);
        else
            return "Nenhum aluno cadastrado para esta turma.";

        return $aulas.$frequencias;
    }

    public function gerarPorFichaView($id = 0){
        if($id != 0)
            $ficha = \App\Models\FichaTecnica::find($id);         
        else
            $ficha = \App\Models\FichaTecnica::where('status','secretaria')->first();
        
        if(!isset($ficha->id))
        return redirect('/fichas')->with('warning','Nenhuma ficha com status de secretaria encontrada.');

        
        $programas=Programa::get();
        $requisitos=RequisitosController::listar();
        $professores=PessoaDadosAdministrativos::getEducadores();
        $unidades=Local::get(['id' ,'nome']);
        $salas = \App\Models\Sala::where('local',$ficha->local)->get();
        $parcerias=Parceria::orderBy('nome')->get();
        $pacote_cursos =\App\Models\PacoteCurso::get();


        return view('turmas.gerar-por-ficha')
                    ->with('ficha',$ficha)
                    ->with('programas',$programas)
                    ->with('professores',$professores)
                    ->with('unidades',$unidades)
                    ->with('salas',$salas)
                    ->with('parcerias',$parcerias)
                    ->with('requisitos',$requisitos)
                    ->with('pacote_cursos',$pacote_cursos);
        
    }

    public function forcarExclusao($turma_id){
        $turma = Turma::find($turma_id);
        if(isset($turma->id)){
            $inscricoes=Inscricao::where('turma',$turma->id)->get();
            foreach($inscricoes as $inscricao){
                \App\Models\Matricula::destroy($inscricao->matricula);
                $inscricao->delete();
            }
            $turma->delete();
            TurmaLogController::registrar('turma',$turma->id,'Turma excluída por duplicidade', \Auth::user()->pessoa);
            
        }
        return "turma ".$turma_id." excluída com sucesso.";
    }

    

   

   
    

  
        
        



}

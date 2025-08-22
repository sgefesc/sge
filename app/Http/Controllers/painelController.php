<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\classes\Data;
use App\Models\Pessoa;
use App\Models\Local;
use App\Models\Turma;
use App\Models\Matricula;
use App\Models\PessoaDadosAcesso;
use App\Http\Controllers\PessoaController;
use Session;
use Auth;
use Illuminate\Support\Facades\DB;
use LancamentoContoller;
use stdClass;
use Carbon\Carbon;



class painelController extends Controller
{


    public function index(){

            if(!Auth::check())
             return redirect('login');
        
        $hoje=new Data();
        $data=$hoje->getData();        
        $dados=['data'=>$data];
        
        $user = Auth::user();
        $pessoa = Pessoa::find($user->pessoa);
        //dd(substr($pessoa->nascimento,5,5));
        if($pessoa){
            if(date('m-d') == substr($pessoa->nascimento,5,5))
                $aniversariante = true;
            else
                $aniversariante = false;

        }
        else
            $aniversariante = false;

        if(realpath($_SERVER['DOCUMENT_ROOT'].'/img/home.png'))
            $img_ext = 'png';
        else
            $img_ext = 'jpg';
            
            

        if(in_array('18',$user->recursos)){
            $pendencias = \App\Models\PessoaDadosGerais::where('dado',20)->paginate(10);
            return view('home', compact('dados'))
                ->with("pendencias",$pendencias)
                ->with('aniversariante',$aniversariante)
                ->with('img_ext',$img_ext);

        }
        return view('home', compact('dados'))
            ->with('aniversariante',$aniversariante)
            ->with('img_ext',$img_ext);
	
    }

    public function indexDev(){
        return view('desenvolvimento.home');
    } 

    
    public function verTurmasAnterioresCursos(){
        
        $db_turmas=DB::table('tb_turmas')->join('tb_cursos', 'tb_turmas.CurCod','=','tb_cursos.CurCod')->where('tb_turmas.TurDatIni','>','2017-06-01')->where('tb_cursos.CurCod','!=','1416')->whereIn('tb_turmas.LocCod', [1,2,69])->get(['tb_turmas.TurCod','tb_turmas.TurDatIni','tb_cursos.CurDsc','tb_turmas.TurDsc','tb_turmas.LocCod','tb_turmas.ProCod']);

        //return $db_turmas;
        $turmas_novas=Turma::where('curso','!=','307')->orderBy('programa','dias_semana','hora_inicio')->get();
        return view('admin.migrarturmas',compact('db_turmas'))->with('nova',$turmas_novas);
       
    }
    public function gravarMigracao(Request $r){
        foreach($r->turma as $navka=>$sge){
            if($sge>0){
                $turma[$navka]=array();
                $alunos_navka=DB::table('tb_matriculas')->where('TurCod',$navka)->get(['AluCod']);
                foreach($alunos_navka as $aluno){
                    $matricula= new Matricula;
                    $matricula->pessoa = $aluno->AluCod;
                    $matricula->atendimento=51;
                    $matricula->status="pendente";
                    $matricula->dia_venc=7;
                    $matricula->forma_pgto="boleto";
                    $matricula->parcelas=5;
                    $matricula->turma=$sge;
                    $matricula->save();

                    array_push($turma[$navka], $matricula);
                }
            }
        }

 

        return $turma;
   
    }
    public function verTurmasAnterioresAulas(){
        // listar as turmas
        $db_aulas=DB::select("select distinct(AulCod) from tb_matriculas m join tb_matriculas_aulas a on a.MatCod = m.MatCod where MatDat > '2017-06-01' order by AulCod");
  
        foreach($db_aulas as $aula){

            //para cada turma, verificar as matriculas daquela turma no periodo fornecido
            $db_turma=DB::select("select AluCod from tb_matriculas m join tb_matriculas_aulas a on a.MatCod = m.MatCod where MatDat > '2017-06-01' and AulCod = ".$aula->AulCod);
            
                       
            if($db_aulas->count()){
                foreach($db_turma as $turma){
                    $alunos[$aula->AulCod][] = $turma->AluCod;                    

                }
            }
        }

        return count($alunos);
    }


    public function administrativo(){
        return view('admin.home');
    }



    /**
     * Painel de chamadas docentes
     *
     * @param integer $id
     * @param integer $semestre
     * @return void
     */
    public function docentes($id=0,$semestre=0){
        if($id == 0){
            $id = Auth::user()->pessoa;
        }

        //return  \App\Models\Utils\WeekHandler::toNumber('seg');
        $horarios = array();
        $dias = ['seg','ter','qua','qui','sex','sab'];
        $carga_ativa = Carbon::createFromTime(0, 0, 0, 'America/Sao_Paulo'); ;
        //$carga_ativa = 0;

        $turmas = \App\Models\Http\Controllers\TurmaController::listarTurmasDocente($id,$semestre);
        $jornadas = \App\Models\Http\Controllers\JornadaController::listarDocente($id,$semestre);
        
        //$jornadas = \App\Models\Jornada::where('pessoa',$id)->get();
        $jornadas_ativas = $jornadas->where('status','ativa');

        $locais = \App\Models\Local::select(['id','nome'])->orderBy('nome')->get();
        
        $carga = \App\Models\PessoaDadosJornadas::where('pessoa',$id)
                ->where(function($query){
                    $query->where('termino', null)->orwhere('termino','0000-00-00');
                })
                ->orderByDesc('id')
                ->first();
        $ghoras_turmas = array();
        $ghoras_HTP = array();
        $ghoras_projetos = array();
        $ghoras_coordenacao = array();
        $ghoras_outros = array();
        $glocais = array();

        //dd($carga);

        foreach($turmas as $turma){
            foreach($turma->dias_semana as $dia){
                $sala = \App\Models\Sala::find($turma->sala);
                if($sala)
                    $turma->nome_sala = $sala->nome;
                else
                    $turma->nome_sala = '';

                $horarios[$dia][substr($turma->hora_inicio,0,2)][substr($turma->hora_inicio,3,2)] = $turma;
                //dd($turma->hora_inicio);
                $inicio = Carbon::createFromFormat('H:i', $turma->hora_inicio);
                $termino = Carbon::createFromFormat('H:i', $turma->hora_termino);
                $carga_ativa->addMinutes($inicio->diffInMinutes($termino));
                switch($dia){
                    case 'seg':
                        $ndia= 1;
                        break;
                    case 'ter':
                        $ndia= 2;
                        break;
                    case 'qua': 
                        $ndia= 3;
                        break;
                    case 'qui': 
                        $ndia= 4;
                        break;
                    case 'sex': 
                        $ndia= 5;
                        break;
                    case 'sab': 
                        $ndia= 6;
                        break;
                    default:
                        $ndia= 0;
                }
                $ghoras_turmas[] = [$ndia,$turma->hora_inicio,$turma->hora_termino,'Turma '.$turma->id,$turma->local->nome];

            }
            if(!in_array($turma->local->sigla,$glocais))
                $glocais[] = $turma->local->sigla;
        }
        //dd($carga_ativa->floatDiffInHours(\Carbon\Carbon::Today()));
        foreach($jornadas_ativas as $jornada){
            foreach($jornada->dias_semana as $dia){
                $horarios[$dia][substr($jornada->hora_inicio,0,2)][substr($jornada->hora_inicio,3,2)] = $jornada;
                $inicio = Carbon::createFromFormat('H:i', $jornada->hora_inicio);
                $termino = Carbon::createFromFormat('H:i', $jornada->hora_termino);
                $carga_ativa->addMinutes($inicio->diffInMinutes($termino));
                switch($dia){
                    case 'seg':
                        $ndia= 1;
                        break;
                    case 'ter':
                        $ndia= 2;
                        break;
                    case 'qua': 
                        $ndia= 3;
                        break;
                    case 'qui': 
                        $ndia= 4;
                        break;
                    case 'sex': 
                        $ndia= 5;
                        break;
                    case 'sab': 
                        $ndia= 6;
                        break;
                    default:
                        $ndia= 0;
                }
                $ghoras_turmas[] = [$ndia,$jornada->hora_inicio,$jornada->hora_termino,$jornada->tipo,$jornada->getLocal()->nome];

            }
        }

        $docente = \App\Models\Pessoa::withTrashed()->find($id);
        $semestres = \App\Models\classes\Data::semestres();
        
        return view('docentes.home')
            ->with('turmas',$turmas)
            ->with('semestres',$semestres)
            ->with('semestre_selecionado',$semestre)
            ->with('docente',$docente)
            ->with('horarios',$horarios)
            ->with('dias',$dias)
            ->with('locais',$locais)
            ->with('glocais',$glocais)
            ->with('carga',$carga)
            ->with('carga_ativa',$carga_ativa)
            ->with('ghoras_turmas',$ghoras_turmas)
            ->with('jornadas',$jornadas);
            




    }

    
    public function financeiro(){
        return view('financeiro.home');
    }
    public function gestaoPessoal(){

        return view('gestaopessoal.inicio-atendimento');
    }
    public function atendimentoPessoal(){
        if(session('rh_atendimento')){            
            $pessoa=session('rh_atendimento');
            return view('gestaopessoal.home')->with('pessoa',$pessoa);
        }

        return view('gestaopessoal.home');
    }
    public function importarLocais(){
        $db_locais=DB::select('select * from tb_localizacoes order by LocDsc');

        foreach($db_locais as $db_local){
            $local=new Local();
            $local->nome=$db_local->LocDsc;
            $local->sigla=$db_local->LocSig;
            $local->save();
        }
        return "Locais importados com sucesso.";
    }       
    
    public function atendimentoPessoalPara($id=0){
        if($id>0){
            session('rh_atendimento',$id);
        }
        else{
            $id=session('rh_atendimento'); 
        }
        
        $pessoa=Pessoa::withTrashed()->find($id);
        // Verifica se a pessoa existe
        if(!$pessoa)
            return redirect(asset('/gestaopessoal/inicio-atendimento'));
        else
            Session::put('rh_atendimento',$id);


        $pessoa_controller= new PessoaController;
        $pessoa=$pessoa_controller->formataParaMostrar($pessoa);
        $pessoa_acesso=PessoaDadosAcesso::where('pessoa',$pessoa->id)->first();
        if(!$pessoa_acesso)
            $pessoa_acesso=0;
        $pessoa->acesso=$pessoa_acesso;
        $pessoa->relacoes_institucionais = \App\Models\PessoaDadosAdministrativos::where('dado','relacao_institucional')->where('pessoa',$pessoa->id)->get();
        $programas = \App\Models\Programa::whereIn('id',[1,2,3,4,12])->orderby('nome')->get();
        $vinculo_programas = \App\Models\PessoaDadosAdministrativos::where('dado','programa')->where('pessoa',$pessoa->id)->get();
        //$carga = \App\Models\PessoaDadosAdministrativos::where('dado','carga_horaria')->where('pessoa',$pessoa->id)->first();

        $carga = \App\Models\PessoaDadosJornadas::where('pessoa',$pessoa->id)->where('status','ativa')->first();



        return view('gestaopessoal.atendimento', compact('pessoa'))
            ->with('programas',$programas)
            ->with('carga',$carga)
            ->with("vinculo_programas",$vinculo_programas);
    }



    public function juridico(){
        return view('juridico.home');
    }

    public function pedagogico(){
        $user = Auth::user();
        $professores_dos_programas = collect();
        $programas = \App\Models\PessoaDadosAdministrativos::where('pessoa',$user->pessoa)->where('dado','programa')->pluck('valor')->toArray();
        $professores = \App\Models\PessoaDadosAdministrativos::getEducadores();
        foreach($professores as $professor){
            $comparisson = array_intersect($programas,$professor->getProgramas());
            if(count($comparisson))
                $professores_dos_programas->push($professor);
        }
        
        return view('pedagogico.home')->with('professores',$professores_dos_programas)->with('programas',$programas);
    }

    public function novoPedagogico(){
        $user = Auth::user();
        $professores_dos_programas = collect();
        $programas = \App\Models\PessoaDadosAdministrativos::where('pessoa',$user->pessoa)->where('dado','programa')->pluck('valor')->toArray();
        $professores = \App\Models\PessoaDadosAdministrativos::getEducadores();
        foreach($professores as $professor){
            $comparisson = array_intersect($programas,$professor->getProgramas());
            if(count($comparisson))
                $professores_dos_programas->push($professor);
        }
        
        return view('pedagogico.novo')->with('professores',$professores_dos_programas)->with('programas',$programas);
    }



    public function secretaria(){
        if(session('pessoa_atendimento')){            
            $pessoa=session('pessoa_atendimento');
            return view('secretaria.home')->with('pessoa',$pessoa);
        }

        return view('secretaria.home');
    }
    public function salasDaUnidade($unidade){
        $salas=Local::where('unidade', 'like', '%'.$unidade.'%')->get();
        return $salas;

    }
    
    



    public function smsRecado(){
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment;filename="'. 'aviso-sms' .'.txt"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');

        $linha='Cadastros com Celular'."\n";
        $erros="\n"."\n".'Cadastros SEM Celular'."\n";


        $turmas = \App\Models\Turma::where('professor',20477)->where('status','iniciada')->get();
        foreach($turmas as $turma){
            $inscricoes = \App\Models\Inscricao::where('turma',$turma->id)->get();
            foreach ($inscricoes as $inscricao){
                $pessoa = Pessoa::find($inscricao->pessoa->id);
                if($pessoa->getCelular() != '-')
                    $linha.= $pessoa->getCelular().';'.$pessoa->nome_simples."\n";
                else{
                    $telefones = $pessoa->getTelefones();
                    $all='';
                    foreach($telefones as $telefone){
                        $all .= $telefone->valor.'  ';  
                    }
                    
                    $erros.= "\n".$pessoa->nome_simples.' : '.$all;
                }

            }
        }

        return $linha.$erros;

    }
   
    public function relatorioJson(){

        header('Contet-Type: text/csv');
        header('Content-Disposition: attachment; filename="alunos-cj.csv"');
        header("Pragma: no-cache");
        header("Expires: 0");
        $arquivo = fopen('php://output','wb');

        $pessoas = Array();
        $turmas  = Turma::whereYear('data_inicio',2019)->whereIn('local',[51,52,53])->get();
        foreach($turmas as $turma){
            $turma->getInscricoes(null);
            foreach($turma->inscricoes as $inscricao){
                $pessoa = Pessoa::withTrashed()->find($inscricao->pessoa->id);
                $pessoa->idade = $pessoa->getIdade();
                unset($pessoa->id);
                unset($pessoa->genero);
                unset($pessoa->nascimento);
                unset($pessoa->por);
                unset($pessoa->created_at);
                unset($pessoa->updated_at);
                unset($pessoa->deleted_at);
                $pessoa->curso = $turma->curso->nome;
                if($pessoa->idade < 21)                    
                    fputcsv($arquivo,json_decode(json_encode($pessoa),true));
            }
        }
        fclose($arquivo);
        

        

             
    }

    /**
     * Cancelamento de pessoas com débitos de dívida ativa
     *
     * @return void
     */
    public static function cancelandoPendentes(){
        $opa = [
            897,
            33523,
            34150,
            34349,
            34380
        ];
        $pendentes = \App\Models\PessoaDadosAdministrativos::select('pessoa')->where('dado','pendencia')->where('valor','like','Falta atestado de vacinação aprovado.')->groupBy('pessoa')->pluck('pessoa')->toArray();

        foreach($pendentes as $pendente){
            $matriculas = \App\Models\Matricula::where('pessoa',$pendente)->where('status','pendente')->pluck('id')->toArray();
           /* $inscricoes = \App\Models\Inscricao::leftjoin('turmas', 'turmas.id','=','inscricoes.turma')->where('pessoa',$pendente)->where('inscricoes.status','pendente')->where('turmas.local',118)->get();
            if($inscricoes->count()>0)
                $opa[] = $pendente;*/
            if(!in_array($pendente,$opa)){
                MatriculaController::alterarStatus(implode(',',$matriculas),'cancelada');
                AtendimentoController::novoAtendimento("Matrícula(s) ".implode(',',$matriculas)." cancelada(s) por falta de comprovante de vacinação.", $pendente);


            }
            //$pendencia_vacina = \App\Models\PessoaDadosAdministrativos::where('pessoa',$pendente)->where('dado','pendencia')->where('valor','like','Falta atestado de vacinação aprovado.')->delete();
            //$pendencia_atestado = \App\Models\PessoaDadosAdministrativos::where('pessoa',$pendente)->where('dado','pendencia')->where('valor','like','Falta atestado de saúde aprovado.')->delete();


        }
        return $pendentes;

    }

    public function atribuirCredencial(){
        $counter = 0;
        $professores = \App\Models\PessoaDadosAdministrativos::getFuncionarios(['Educador','Educador de Parceria']);
        foreach($professores as $professor){
            if($professor->id>0){
                $recurso = \App\Models\ControleAcessoRecurso::where('pessoa',$professor->id)->where('recurso',29)->first();
                if($recurso)
                    continue;
                else {
                $recurso = new \App\Models\ControleAcessoRecurso;
                $recurso->pessoa = $professor->id;
                $recurso->recurso = 29;
                $recurso->save();
                $counter++;
                }
            }

        }

        return "Acesso a ".$counter." professores ao recurso de ficha tecnica liberado";
    }

    public function testarClasse(){
        dd(\App\Models\Http\Controllers\DiaNaoLetivoController::cadastroAnual());
        



       
		return "Os boletos serão gerados";
    }
    
    /**
     * Verifica os cpfs do arquivo excel para colocar pendencia de divida ativa no cadastro
     *
     * @return void
     */
    public function dividaAtiva(){

        
        $input='./documentos/g1.xlsx';
        $cpfs = array();
        //$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($input);
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($input);
        for($i=2;$i<=140;$i++){
            
            $cpf=$spreadsheet->getActiveSheet()->getCell('A'.$i)->getValue();
            
            $pessoa = \App\Models\PessoaDadosGerais::where('dado',3)->where('valor',$cpf)->first();
            //dd($pessoa);
            if(isset($pessoa->id)){
                $pendencia = new \App\Models\PessoaDadosAdministrativos;
                $pendencia->pessoa = $pessoa->pessoa;
                $pendencia->dado = 'pendencia';
                $pendencia->valor = 'Dívida ativa';
                $pendencia->save();
                array_push($cpfs,$pessoa->pessoa);
                /*
                $spreadsheet->getActiveSheet()->getCell('C'.$i)->setValue($pessoa->id);
                array_push($cpfs,$pessoa->id);
                $matricula = \App\Models\Matricula::where('pessoa',$pessoa->id)->where('status','ativa')->first();
                if(isset($matricula->id))
                    $spreadsheet->getActiveSheet()->getCell('D'.$i)->setValue('Matricula ativa');
                */
            }
                


        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($input);
        return $cpfs;
        



        /*
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();

        foreach ($routeCollection as $value) {
            echo $value->uri."<br>";
        } 



        //\App\Models\DividaAtiva::gerarLivroCorrente();
        //return painelController::cancelandoPendentes();
   
        
       
       
       /* -------------------  selecionando turmas de atividade física
        $turmas_atestado = \App\Models\CursoRequisito::select('cursos_requisitos.*','turmas.id as id_turma')
                ->leftjoin('turmas', 'turmas.id','=','cursos_requisitos.curso')
                ->where('turmas.status','lancada')
                ->where('para_tipo','turma')
                ->where('requisito','18')
                ->pluck('id_turma')->toArray();
        // ---------------------------------*/


        // -- varre as inscrições procurando por pendentes e gera uma lista de nomes/emails em json
        /*
        $inscricoes = \App\Models\Inscricao::whereIn('status',['regular','pendente'])->get();
        $pessoas_array = array();
        foreach($inscricoes as $inscricao){
            $estado = AtestadoController::verificaParaInscricao($inscricao->pessoa->id,$inscricao->turma);
            if($estado)
                $inscricao->status = 'regular';
            else{
                $inscricao->status = 'pendente';
                if(!in_array($inscricao->pessoa->id,$pessoas_array))
                    $pessoas_array[] = $inscricao->pessoa->id;
            }
            
            $inscricao->save();
            if($inscricao->matricula>0)
                MatriculaController::atualizar($inscricao->matricula);


        }

        $pessoas = \App\Models\Pessoa::select('id','nome')
            ->whereIn('id',$pessoas_array) ->get();

        foreach($pessoas as &$pessoa){
            $email = \App\Models\PessoaDadosContato::where('pessoa',$pessoa->id)->where('dado',1)->orderbyDESC('id')->first();
            if($email)
                $pessoa->email = $email->valor;
            else
                $pessoa->email = 'indefinido';

            
            $pessoa->celular = $pessoa->getCelular();
        }
        

        
        foreach($pessoas as $pessoa){
            $matriculas = Matricula::where('pessoa',$pessoa)->where('curso',307)->whereIn('status',['pendente','ativa'])->pluck('id')->toArray();
            $inscricoes = \App\Models\Inscricao::whereIn('matricula',$matriculas)->get();
            foreach($inscricoes as $inscricao){
                $inscricao->matricula = $matriculas[0];
                $inscricao->save();
            }
            foreach($matriculas as $matricula){
                MatriculaController::atualizar($matricula);
            }
            
        }
        */

        


        //return $this->listarAlunosPendentesDocumentos();

    }

    public function listarAlunosPendentesDocumentos(){
        //$inscricoes = \App\Models\Inscricao::where('status','pendente')->get();
        $pendentes = \App\Models\PessoaDadosAdministrativos::select('pessoa')->where('dado','pendencia')->whereIn('valor',['Falta atestado de vacinação aprovado.','Falta atestado de saúde aprovado.'])->groupBy('pessoa')->pluck('pessoa')->toArray();

       

        $pessoas = \App\Models\Pessoa::select('id','nome')
            ->whereIn('id',$pendentes) ->get();

        foreach($pessoas as &$pessoa){
            $email = \App\Models\PessoaDadosContato::where('pessoa',$pessoa->id)->where('dado',1)->orderbyDESC('id')->first();
            if($email)
                $pessoa->email = $email->valor;
            else
                $pessoa->email = 'indefinido';

            $telefone = \App\Models\PessoaDadosContato::where('pessoa',$pessoa->id)->where('dado',2)->first();
            if($telefone)
                $pessoa->telefone = $telefone->valor;
            $pessoa->celular = $pessoa->getCelular();
        }

    return $pessoas;

    }

    


    public function listarPendentesComMatriculas(){

        $pendentes = \App\Models\PessoaDadosAdministrativos::select('pessoa')->where('dado','pendencia')->where('valor','like','Falta atestado de vacinação aprovado.')->groupBy('pessoa')->pluck('pessoa')->toArray();
        $pessoas = \App\Models\Pessoa::select('id','nome')->whereIn('id',$pendentes)->get();
        $pessoas_com_matriculas = collect();
        foreach($pessoas as &$pessoa){
            $matriculas = \App\Models\Matricula::where('pessoa',$pessoa->id)->whereIn('status',['pendente','ativa'])->count();
            if($matriculas>0){
                $pessoa->celular = $pessoa->getCelular();
                $pessoas_com_matriculas->add($pessoa);

            }
            else{
                \App\Models\PessoaDadosAdministrativos::where('pessoa',$pessoa->id)->where('dado','pendencia')->where('valor','like','Falta atestado de vacinação aprovado.')->delete();
            }
               



           
        }


        return $pessoas_com_matriculas;
        // ---------------------------------------------------------------------------*/

       

    }


    public function testarClassePost(Request $r){
        
      


    }

    public function rematricula(){
        
    }

    public function alertaCovid(){
        $CC = new ContatoController;
        //$msg = "FESC INFORMA: Aulas suspensas por tempo indeterminado. Saiba mais no site fesc.com.br";
        //$msg = "FESC INFORMA: Aulas suspensas A PARTIR DO DIA 17/03 por tempo indeterminado. Duvidas? Ligue 3362-0580";
        //$msg = "FESC INFORMA: Prezados alunos, os boletos do mês de maio, com vencimento em 10/05 serão cancelados.Fique seguro, fique em casa.";
        //$msg = "FESC INFORMA: Prezados alunos, os boletos do mês de julho, com vencimento em 10/07 serão cancelados.Fique seguro, fique em casa.";
        $msg = "FESC INFORMA: Prezados alunos, os boletos do mês de agosto, com vencimento em 10/08 serão cancelados.Fique seguro, fique em casa.";
        $matriculas=Matricula::where('status','ativa')->groupBy('pessoa')->get();
        foreach($matriculas as $matricula){
            $this->dispatch(new \App\Models\Jobs\EnviarSMS($msg,$matricula->pessoa));
            //$CC->enviarSMS($msg,[$matricula->pessoa]);
        }
        return "Notificações enviadas";

    }


    //essa função atualiza os boletos com divida ativa
    public function importarStatusBoletos(){
        $boletos_alterados=collect();
        $input='./documentos/dividas_2018.xlsx';
        //$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($input);
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($input);
        for($i=2;$i<=162;$i++){
            $insc= (object)[];
            $insc->cpfAlu=$spreadsheet->getActiveSheet()->getCell('B'.$i)->getValue();
            $insc->boletoVenc=$spreadsheet->getActiveSheet()->getCell('C'.$i)->getFormattedValue();
            
            $insc->boletoVenc = \DateTime::createFromFormat('d/m/Y',$insc->boletoVenc);
            //dd($insc);
            $pessoa_db=\App\Models\PessoaDadosGerais::where('valor', $insc->cpfAlu)->first();

            if(isset($pessoa_db->pessoa) && $pessoa_db->pessoa>0){
               
                $boleto = \App\Models\Boleto::where('pessoa',$pessoa_db->pessoa)->where('vencimento','like',$insc->boletoVenc->format('Y-m-d').'%')->first();

                //$boletos_alterados[] = $boleto.$pessoa_db->pessoa.'.'.$insc->boletoVenc->format('Y-m-d');
                if(isset($boleto->id)){
                    $boletos_alterados[] = $boleto;
                    if($boleto->status != $spreadsheet->getActiveSheet()->getCell('F'.$i)->getValue()){
                        $boleto->status = strtolower($spreadsheet->getActiveSheet()->getCell('F'.$i)->getValue());
                        $boleto->save();
                        LogController::alteracaoBoleto($boleto->id,'Processamento em lote D.A. Navka em '.date('d/m/Y').': '.$spreadsheet->getActiveSheet()->getCell('F'.$i)->getValue());
                    }
                }
            }
            

        }
        return $boletos_alterados;

    }
 

    	
}
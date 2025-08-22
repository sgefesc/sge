<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FichaTecnica;
use App\Models\FichaTecnicaDados;
use App\Models\Programa;
use App\Models\Pessoa;
use App\Models\PessoaDadosAdministrativos;
use App\Models\Local;
use App\Models\Sala;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Auth;

class FichaTecnicaController extends Controller
{
    /**
     * O dados das fichas são liberados de acordo 2 recursos: docentes - 13 - e secretaria - 17
     * 
     *  No caso de professor ele verifica se tem o recurso docente não tem o cordenador
     *  No caso do coordenador, ele com certeza terá os dois
     *  No caso da secretaria ele não cairá em nenhum dos casos acima, pois com certeza ele não terá o recurso de docente.
     * 
     */



    public function index($filtro=null,$valor=null,$rem_filtro=null,$remove=0,$ipp=50){

        session_start();
        //dd($_GET['rem_filtro']);
        $view=null;

 
        
        if(isset($_SESSION['filtro_fichas']))
            $filtros = $_SESSION['filtro_fichas'];
        
        else
            $filtros = array();



        if(!isset($filtros['status']) && !isset($_GET['filtro'])){
            $filtros['status'] = ['docente','coordenacao','diretoria','administracao','presidencia','secretaria'];
            
        }
        

    
        
            
        if(isset($_GET['filtro']) && isset($_GET['valor'])){
           
            if(array_key_exists($_GET['filtro'], $filtros)){
                
                if(array_search($_GET['valor'], $filtros[$_GET['filtro']]) === false){
                    $filtros[$_GET['filtro']][] = $_GET['valor'];
                }
                else
                {
                    if(isset($_GET['remove']) && $_GET['remove'] > 0){
                        unset($filtros[$_GET['filtro']][array_search($_GET['valor'], $filtros[$_GET['filtro']])]);
                    }
                }
            }
            else{
                $filtros[$_GET['filtro']][] = $_GET['valor'];
            }
            
        }
        if(isset($_GET['removefiltro']) && $_GET['removefiltro'] != null){
            if(isset($filtros[$_GET['removefiltro']]))
                unset($filtros[$_GET['removefiltro']]);
        }

        //dd($filtro);
        

        $_SESSION['filtro_fichas'] = $filtros;
        
        $fichas = FichaTecnica::where('id','>','1');
        $programas=Programa::whereIn('id',[1,2,3,4,12])->orderBy('sigla')->get();
        $professores = \App\Models\PessoaDadosAdministrativos::getEducadores();

        if(in_array('13', Auth::user()->recursos) && !in_array('17', Auth::user()->recursos)){
            $fichas = FichaTecnica::where('docente',Auth::user()->pessoa);
            $programa = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $programas = Programa::whereIn('id',$programa)->get();
            $professores = Pessoa::where('id',Auth::user()->pessoa)->get();

        }
        if(in_array('17', Auth::user()->recursos) && in_array('13', Auth::user()->recursos)){
            //dd('aqui');
            $programa = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $programas = Programa::whereIn('id',$programa)->get();
            $professores_dos_programas = collect();

            
            foreach($professores as $professor){
                $comparisson = array_intersect($programa,$professor->getProgramas());
                if(count($comparisson))
                    $professores_dos_programas->push($professor);
            }
            $professores = Pessoa::whereIn('id', $professores_dos_programas->pluck('id')->toArray())->get();
            $professores = $professores->sortBy('nome');
            $programas_ficha = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $fichas = FichaTecnica::whereIn('programa',$programas_ficha);
        }

    
        
        if(isset($_GET['busca'])){
            
            $fichas = $fichas->where('id',$_GET['busca'])->orwhere('curso','like','%'.$_GET['busca'].'%');

        }

        

        if(isset($filtros['programa']) && count($filtros['programa'])){
            $fichas = $fichas->whereIn('programa', $filtros['programa']); 
        }

        if(isset($filtros['professor']) && count($filtros['professor'])){
            $fichas = $fichas->whereIn('docente', $filtros['professor']); 
        }
        if(isset($filtros['local']) && count($filtros['local'])){
            $fichas = $fichas->whereIn('local', $filtros['local']); 
        }

        if(isset($filtros['status']) && count($filtros['status'])){
            $fichas = $fichas->whereIn('status', $filtros['status']); 
        }
        
        
        $fichas = $fichas->paginate($ipp);
        $locais = Local::all();

       

        

        return view('fichas-tecnicas.index')
            ->with('programas',$programas)
            ->with('locais',$locais)
            ->with('professores',$professores)
            ->with('filtros',$_SESSION['filtro_fichas'])
            ->with('view',$view)
            ->with('fichas',$fichas);

    }
    public function pesquisar(Request $r){
        $fichas = FichaTecnica::where('curso','like','%'.$r->curso.'%')->paginate(50);
        return view('fichas-tecnicas.index')->with('fichas',$fichas);

    }

    public function cadastrar(){
        
        $unidades=Local::get(['id' ,'nome']);
        $programas = Programa::all();
        $professores = PessoaDadosAdministrativos::getFuncionarios(['Educador','Educador Temporário','Educador Terceirizado','Educador de Parceria']);

        if(in_array('13', Auth::user()->recursos) && !in_array('17', Auth::user()->recursos)){
            $programa = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $programas = Programa::whereIn('id',$programa)->get();
            $professores = Pessoa::where('id',Auth::user()->pessoa)->get();

        }
        if(in_array('17', Auth::user()->recursos) && in_array('13', Auth::user()->recursos)){
            $programa = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $programas = Programa::whereIn('id',$programa)->get();
            $professores_dos_programas = collect();
            $professoress = \App\Models\PessoaDadosAdministrativos::getFuncionarios('Educador','Educador Temporário','Educador Terceirizado','Educador de Parceria');
            foreach($professoress as $professor){
                $comparisson = array_intersect($programa,$professor->getProgramas());
                if(count($comparisson))
                    $professores_dos_programas->push($professor);
            }
            $professores = Pessoa::whereIn('id', $professores_dos_programas->pluck('id')->toArray())->get();
            $professores = $professores->sortBy('nome');
            //dd($professores);

        }

        return view('fichas-tecnicas.cadastrar')
                ->with('professores',$professores)
                ->with('unidades',$unidades)
                ->with('programas',$programas);
    }

    public function gravar(Request $r){
        $ficha = new FichaTecnica;
        $ficha->programa = $r->programa;
        $ficha->docente = $r->professor;
        $ficha->curso = mb_strtoupper($r->curso,'UTF-8');
        $ficha->objetivo = $r->objetivos;
        $ficha->conteudo = $r->conteudo;
        $ficha->requisitos = $r->requisitos;
        $ficha->idade_minima = $r->idade_min;
        $ficha->idade_maxima = $r->idade_max;
        $ficha->carga = $r->carga;
        $ficha->periodicidade = $r->periodicidade;
        $ficha->dias_semana = implode(',',$r->dias);
        $ficha->data_inicio = $r->data_inicio;
        $ficha->data_termino = $r->data_termino;
        $ficha->hora_inicio = $r->hora_inicio;
        $ficha->hora_termino = $r->hora_termino;
        $ficha->lotacao_maxima = $r->lotacao_max;
        $ficha->lotacao_minima = $r->lotacao_min;
        $ficha->valor = str_replace(',','.',$r->valor)*100;
        $ficha->local = $r->local;
        $ficha->sala = $r->sala;
        $ficha->status = 'docente';
        $ficha->materiais = $r->materiais;
        $ficha->obs = $r->obs;
        $ficha->save();

        $dados_ficha = new FichaTecnicaDados;
        $dados_ficha->ficha = $ficha->id;
        $dados_ficha->dado = 'log';
        $dados_ficha->conteudo = 'Cadastro efetuado';
        $dados_ficha->agente = Auth::user()->pessoa;
        $dados_ficha->save();

        if($r->btn==2)
            return redirect()->back()->with('success','Ficha cadastrada com sucesso');
        else
            return redirect('/fichas')->with('success','Ficha cadastrada com sucesso');

    }

    public function visualizar($id){
        $relacao = PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','relacao_institucional')->pluck('valor')->toArray();
        //dd($relacao->valor);
        $ficha = FichaTecnica::find($id);
        $dados_ficha = FichaTecnicaDados::where('ficha',$ficha->id)->get();
        $administrativo = array('Coordenador de Programa','Contador','Presidente','Diretor','Auxiliar Administrativo');
        $adm = false;
        foreach($relacao as $ri)
            if(in_array($ri,$administrativo))
                $adm = true;
       


        return view('fichas-tecnicas.exibir',compact('ficha'))
            ->with('adm',$adm)
            ->with('dados',$dados_ficha);
    
                    
    }

    public function imprimir($id){
        $fichas =  explode(',',$id);
        $fichas = FichaTecnica::whereIn('id',$fichas)->get();
        
        return view('fichas-tecnicas.imprimir',compact('fichas'));
            
    }


    public function editar($id){
        $ficha = FichaTecnica::find($id);
        $unidades = Local::get(['id' ,'nome']);
        $salas = Sala::where('local',$ficha->local)->get();
        
        $programas = Programa::all();
        $professores = PessoaDadosAdministrativos::getFuncionarios(['Educador','Educador Temporário','Educador Terceirizado','Educador de Parceria']);

        if(in_array('13', Auth::user()->recursos) && !in_array('17', Auth::user()->recursos)){
            $programa = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $programas = Programa::whereIn('id',$programa)->get();
            $professores = Pessoa::where('id',Auth::user()->pessoa)->get();

        }
        if(in_array('17', Auth::user()->recursos) && in_array('13', Auth::user()->recursos)){
            $programa = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $programas = Programa::whereIn('id',$programa)->get();
            $professores_dos_programas = collect();
            $professoress = \App\Models\PessoaDadosAdministrativos::getFuncionarios('Educador','Educador Temporário','Educador Terceirizado','Educador de Parceria');
            foreach($professoress as $professor){
                $comparisson = array_intersect($programa,$professor->getProgramas());
                if(count($comparisson))
                    $professores_dos_programas->push($professor);
            }
            $professores = Pessoa::whereIn('id', $professores_dos_programas->pluck('id')->toArray())->get();
            $professores = $professores->sortBy('nome');
            //dd($professores);

        }

        return view('fichas-tecnicas.editar',compact('ficha'))
                ->with('professores',$professores)
                ->with('salas',$salas)
                ->with('unidades',$unidades)
                ->with('programas',$programas);
    }

    public function update(Request $r){
        $ficha = FichaTecnica::find($r->id);
        $edicoes = '';
        if($ficha->programa != $r->programa)
            $edicoes .= 'programa: '.$ficha->programa. ' => '.$r->programa.', ';
        $ficha->programa = $r->programa;
        if($ficha->docente != $r->professor)
            $edicoes .= 'docente: '.$ficha->docente. ' => '.$r->professor.', ';
        $ficha->docente = $r->professor;
        if($ficha->curso !=  mb_strtoupper($r->curso,'UTF-8'))
            $edicoes .= 'curso: '.$ficha->curso. ' => '. mb_strtoupper($r->curso,'UTF-8').', ';
        $ficha->curso = mb_strtoupper($r->curso,'UTF-8');
        if($ficha->objetivo != $r->objetivos)
            $edicoes .= 'objetivo modificado, ';
        $ficha->objetivo = $r->objetivos;
        if($ficha->conteudo != $r->conteudo)
            $edicoes .= 'conteudo modificado, ';
        $ficha->conteudo = $r->conteudo;
        if($ficha->requisitos != $r->requisitos)
            $edicoes .= 'requisitos: '.$ficha->requisitos. ' => '.$r->requisitos.', ';
        $ficha->requisitos = $r->requisitos;
        if($ficha->idade_minima != $r->idade_min)
            $edicoes .= 'idade mínima: '.$ficha->idade_minima. ' => '.$r->idade_min.', ';
        $ficha->idade_minima = $r->idade_min;
        if($ficha->idade_maxima != $r->idade_max)
            $edicoes .= 'idade máxima: '.$ficha->idade_maxima. ' => '.$r->idade_max.', ';
        $ficha->idade_maxima = $r->idade_max;
        if($ficha->carga != $r->carga)
            $edicoes .= 'carga: '.$ficha->carga. ' => '.$r->carga.', ';
        $ficha->carga = $r->carga;
        if($ficha->periodicidade != $r->periodicidade)
            $edicoes .= 'periodicidade: '.$ficha->periodicidade. ' => '.$r->periodicidade.', ';
        $ficha->periodicidade = $r->periodicidade;
        if($ficha->dias_semana != implode(',',$r->dias))
            $edicoes .= 'dias: '.$ficha->dias_semana. ' => '.implode(',',$r->dias).', ';
        $ficha->dias_semana = implode(',',$r->dias);
        if($ficha->data_inicio->format('Y-m-d') != $r->data_inicio)
            $edicoes .= 'data inicio: '.$ficha->data_inicio->format('Y-m-d'). ' => '.$r->data_inicio.', ';
        $ficha->data_inicio = $r->data_inicio;
        if($ficha->data_termino->format('Y-m-d') != $r->data_termino)
            $edicoes .= 'data termino: '.$ficha->data_termino->format('Y-m-d'). ' => '.$r->data_termino.', ';
        $ficha->data_termino = $r->data_termino;
        if($ficha->hora_inicio != $r->hora_inicio)
            $edicoes .= 'hora inicio: '.$ficha->hora_inicio. ' => '.$r->hora_inicio.', ';
        $ficha->hora_inicio = $r->hora_inicio;
        if($ficha->hora_termino != $r->hora_termino)
            $edicoes .= 'hora termino: '.$ficha->hora_termino. ' => '.$r->hora_termino.', ';
        $ficha->hora_termino = $r->hora_termino;
        if($ficha->lotacao_maxima != $r->lotacao_max)
            $edicoes .= 'lotação máxima: '.$ficha->lotacao_maxima. ' => '.$r->lotacao_max.', ';
        $ficha->lotacao_maxima = $r->lotacao_max;
        if($ficha->lotacao_minima != $r->lotacao_min)
            $edicoes .= 'lotação mínima: '.$ficha->lotacao_minima. ' => '.$r->lotacao_min.', ';
        $ficha->lotacao_minima = $r->lotacao_min;
        if($ficha->valor != str_replace(',','.',$r->valor)*100)
            $edicoes .= 'valor: '.$ficha->valor. ' => '.$r->valor.', ';
        $ficha->valor = str_replace(',','.',$r->valor)*100;
        if($ficha->local != $r->local)
            $edicoes .= 'local: '.$ficha->local. ' => '.$r->local.', ';
        $ficha->local = $r->local;
        if($ficha->sala != $r->sala)
            $edicoes .= 'sala: '.$ficha->sala. ' => '.$r->sala.', ';
        $ficha->sala = $r->sala;
        if($ficha->materiais != $r->materiais)
            $edicoes .= 'recursos necessários alterado, ';
        $ficha->materiais = $r->materiais;
        if($ficha->obs != $r->obs)
            $edicoes .= 'mais informações alterado, ';
        $ficha->obs = $r->obs;

        $ficha->save();
       

        $dados_ficha = new FichaTecnicaDados;
        $dados_ficha->ficha = $ficha->id;
        $dados_ficha->dado = 'log';
        $dados_ficha->conteudo = 'Edição realizada - '.$edicoes;
        $dados_ficha->agente = Auth::user()->pessoa;
        $dados_ficha->save();

        return redirect('/fichas')->with('success','Ficha cadastrada com sucesso');

    }

    public function excluir(Request $r){
        $ficha = FichaTecnica::find($r->id);
        if(isset($ficha->id)){
            $ficha->delete();
            return response('Done!',200);

        }
        else        
            return response('Id não encontrado',404);
     }

     public function copiar($id){
       
        $ficha = FichaTecnica::find($id);
        if(!isset($ficha->id))
            return redirect()->back()->with('warning','Ficha não encontrada');
        /*$programas = Programa::all();
        $unidades = Local::get(['id' ,'nome']);
        $salas = Sala::where('local',$ficha->local)->get();
        $professores = PessoaDadosAdministrativos::getFuncionarios(['Educador','Educador de Parceria']);
        return view('fichas-tecnicas.copiar',compact('ficha'))
                ->with('professores',$professores)
                ->with('salas',$salas)
                ->with('unidades',$unidades)
                ->with('programas',$programas);*/
        $unidades = Local::get(['id' ,'nome']);
        $salas = Sala::where('local',$ficha->local)->get();
        
        $programas = Programa::all();
        $professores = PessoaDadosAdministrativos::getFuncionarios(['Educador','Educador Temporário','Educador Terceirizado','Educador de Parceria']);

        if(in_array('13', Auth::user()->recursos) && !in_array('17', Auth::user()->recursos)){
            $programa = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $programas = Programa::whereIn('id',$programa)->get();
            $professores = Pessoa::where('id',Auth::user()->pessoa)->get();

        }
        if(in_array('17', Auth::user()->recursos) && in_array('13', Auth::user()->recursos)){
            $programa = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $programas = Programa::whereIn('id',$programa)->get();
            $professores_dos_programas = collect();
            $professoress = \App\Models\PessoaDadosAdministrativos::getFuncionarios('Educador','Educador Temporário','Educador Terceirizado','Educador de Parceria');
            foreach($professoress as $professor){
                $comparisson = array_intersect($programa,$professor->getProgramas());
                if(count($comparisson))
                    $professores_dos_programas->push($professor);
            }
            $professores = Pessoa::whereIn('id', $professores_dos_programas->pluck('id')->toArray())->get();
            $professores = $professores->sortBy('nome');
            //dd($professores);

        }
        return view('fichas-tecnicas.copiar',compact('ficha'))
                ->with('professores',$professores)
                ->with('salas',$salas)
                ->with('unidades',$unidades)
                ->with('programas',$programas);
    
     }

     public function encaminhar(Request $r){
        $ficha = FichaTecnica::find($r->id);
        if(!isset($ficha->id))
            return response('Não encontrada',404);
        $ficha->status = $r->depto;
        $ficha->save();

        $dados_ficha = new FichaTecnicaDados;
        $dados_ficha->ficha = $ficha->id;
        $dados_ficha->dado = 'log';
        $dados_ficha->conteudo = 'Ficha encaminhada para '.$r->depto. '. '.$r->obs;
        $dados_ficha->agente = Auth::user()->pessoa;
        $dados_ficha->save();
        
        return response('OK',200);

     }

     public function exportar(){
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. 'fichas-tecnicas' .'.xls"'); //$filename is  xsl filename 
        header('Cache-Control: max-age=0');

        $tabela =  new Spreadsheet();
        $arquivo = new Xls($tabela);
        $linha = 2;

        $fichas = FichaTecnica::where('id','>','1');
        if(in_array('13', Auth::user()->recursos) && !in_array('17', Auth::user()->recursos)){
            $fichas = FichaTecnica::where('docente',Auth::user()->pessoa);
        }
        if(in_array('17', Auth::user()->recursos) && in_array('13', Auth::user()->recursos)){
            $programas_ficha = \App\Models\PessoaDadosAdministrativos::where('pessoa',Auth::user()->pessoa)->where('dado','programa')->pluck('valor')->toArray();
            $fichas = FichaTecnica::whereIn('programa',$programas_ficha);
        } 

        $fichas = $fichas->get();

        $planilha = $tabela->getActiveSheet();
        $planilha->setCellValue('A1', 'Ficha');
        $planilha->setCellValue('B1', 'Turma');
        $planilha->setCellValue('C1', 'Dias');
        $planilha->setCellValue('D1', 'Hora início');
        $planilha->setCellValue('E1', 'Hora termino');
        $planilha->setCellValue('F1', 'Disciplina');
        $planilha->setCellValue('G1', 'Educador');
        $planilha->setCellValue('H1', 'Vagas');
        $planilha->setCellValue('I1', 'Local');
        $planilha->setCellValue('J1', 'Sala');
        $planilha->setCellValue('K1', 'Carga');
        $planilha->setCellValue('L1', 'Início');
        $planilha->setCellValue('M1', 'Termino');
        $planilha->setCellValue('N1', 'Valor');
        $planilha->setCellValue('O1', 'Estado');
        $planilha->setCellValue('P1', 'Programa');



        foreach($fichas as $ficha){ 
        	
        
            $planilha->setCellValue('A'.$linha, $ficha->id);
            $planilha->setCellValue('B'.$linha, $ficha->turma);
            $planilha->setCellValue('C'.$linha, $ficha->dias_semana);
            $planilha->setCellValue('D'.$linha, $ficha->hora_inicio);
            $planilha->setCellValue('E'.$linha, $ficha->hora_termino);
            $planilha->setCellValue('F'.$linha, $ficha->curso);
            $planilha->setCellValue('G'.$linha, $ficha->getDocente());
            $planilha->setCellValue('H'.$linha, $ficha->lotacao_maxima);
            $planilha->setCellValue('I'.$linha, $ficha->getLocal());
            $planilha->setCellValue('J'.$linha, $ficha->getSala());
            $planilha->setCellValue('K'.$linha, $ficha->carga);
            if(!$ficha->data_inicio)
                $planilha->setCellValue('L'.$linha, 'ND');
            else
                $planilha->setCellValue('L'.$linha, $ficha->data_inicio->format('d/m/Y'));

            if($ficha->data_termino)
                $planilha->setCellValue('M'.$linha, $ficha->data_termino->format('d/m/Y'));
            else
                $planilha->setCellValue('M'.$linha,'ND');
            $planilha->setCellValue('N'.$linha, $ficha->valor);
            $planilha->setCellValue('O'.$linha, $ficha->status);
            $planilha->setCellValue('P'.$linha, $ficha->getPrograma()->sigla);
           
            $linha++;
            
    }
    
    
    return $arquivo->save('php://output');
     }

}

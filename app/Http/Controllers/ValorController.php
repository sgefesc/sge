<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Valor;
use App\Models\Matricula;
ini_set('max_execution_time', 180);
class ValorController extends Controller
{
    private const vencimento = 10;
	private const data_corte = 20;
	private const dias_adicionais = 5;



    public function cadastrarValores(){
        
        $itens = array();
        $itens[1] = ['programa' => 12,
                     'curso' => 0,
                     'carga' => 40,
                     'referencia' => "Piscina 40h semanal",
                     'valor'=> 294,
                     'parcelas' => 10,
                     'ano' => 2021];
        $itens[2] = ['programa' => 3,
                     'curso' => 307,
                     'carga' => 1,
                     'referencia' => "UATI 1 disciplina",
                     'valor'=> 294,
                     'parcelas' => 10,
                     'ano' => 2021];


        foreach($itens as $item){
            $registro = Valor::where('programa',$item['programa'])->where('curso',$item['curso'])->where('carga',$item['carga'])->where('ano',$item['ano'])->first();
            if(!isset($registro->id)){
                $valor = new Valor;
                $valor->programa = $item['programa'];
                $valor->curso = $item['curso'];
                $valor->carga = $item['carga'];
                $valor->referencia = $item['referencia'];
                $valor->valor=$item['valor'];
                $valor->parcelas = $item['parcelas'];
                $valor->ano = $item['ano'];
                $valor->save();
            }
        }


        return $itens;

    }

    public static function valorMatricula($id_matricula)
    {


        $matricula = Matricula::find($id_matricula);
        
       

    	if($matricula)
    	{
            

            
            

            $inscricoes = \App\Models\Inscricao::where('matricula',$matricula->id)->whereIn('status',['regular','pendente','espera'])->get();
            if($matricula->curso == null){
                \App\Models\Http\Controllers\MatriculaController::matriculaSemCurso($matricula);

            }
            //$inscricao_t = \App\Models\Inscricao::where('matricula',$matricula->id)->first();

            if($inscricoes->count() == 0){
                return ValorController::retornarZero('Não há inscrições ativas');
            }
            $turma = \App\Models\Turma::find($inscricoes->first()->turma->id);
            if(isset($matricula->pacote)){
        
                $valor = Valor::where('pacote',$matricula->pacote)->where('ano',substr($turma->data_inicio,-4))->first();
                if($valor){
                    $valor->valor = ($valor->valor/$valor->parcelas)*$matricula->getParcelas();
                    return $valor;

                }
                    
                else
                    dd('Valor do pacote '.$matricula->pacote.' não encontrado para o ano de '.substr($turma->data_inicio,-4) );

            }

            

            
            //dd($turma->parceria->id);
            $fesc=[84,85,86,118];
            if(!in_array($turma->local->id,$fesc)){
                 return ValorController::retornarZero('Turma fora da fesc');

            }
            if(isset($turma->parceria))
                return ValorController::retornarZero('Parcerias/Ação Social');

            
            if($turma->programa->id == 4)
                return ValorController::retornarZero('Escola Municipal de Governo');




    		if($matricula->curso == 307)
    		{
    			$inscricoes = \App\Models\Inscricao::where('matricula',$matricula->id)->whereIn('status',['regular','pendente'])->get();
    			switch (count($inscricoes)) {
    				case 0:
                         return ValorController::retornarZero('Não há inscrições ativas');
                        break;
                    case 1:
                    	$valor = Valor::where('curso','307')->where('carga','1')->where('ano',substr($inscricoes->first()->turma->data_inicio,-4))->first();
                        if($valor)
                            return $valor;
                        else{
                            //redirect()->back()->withErrors(['teste']);
                            return ValorController::retornarZero("Pacote não definido para turma ".$turma->id);

                        }
                            
                        break;
                    case 2:
                    case 3:
                    
                        $valor = Valor::where('curso','307')->where('carga','2')->where('ano',substr($inscricoes->first()->turma->data_inicio,-4))->first();
                        if($valor)
                            return $valor;
                        else
                            return ValorController::retornarZero("Pacote não definido para turma ".$turma->id);
                        break;
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                    case 10:
                        $valor = Valor::where('curso','307')->where('carga','3')->where('ano',substr($inscricoes->first()->turma->data_inicio,-4))->first();
                        if($valor)
                            return $valor;
                        else
                            return ValorController::retornarZero("Pacote não definido para turma ".$turma->id);
                        break;
    			}
    			

    		}
    		else //não é 307
    		{
               /*
    			$inscricao = \App\Models\Inscricao::where('matricula',$matricula->id)->first();
                if($inscricoes->count()==0){
                    return ValorController::retornarZero('Não há inscrições ativas');
                

                else*/
                $valor = new Valor;
                $valor->valor =0;
                foreach($inscricoes as $inscricao){
                    $turma= \App\Models\Turma::find($inscricao->turma->id); 
                    $valor->valor += $turma->valor;
                    
                    
                }
                

                
                if($valor->valor>0){
                    $valor->parcelas = $turma->getParcelas();
                    $valor->referencia = 'parcelas temporaria';
                    //return $valor;
                }
                //dd($matricula->getParcelas());

                if($valor->valor>0 && $valor->parcelas>0 && $matricula->getParcelas()>0){
                    $valor->valor = ($valor->valor/$valor->parcelas)*$matricula->getParcelas();
                    return $valor;
                }
                else
                    {
                    return ValorController::retornarZero("Pacote não definido para turma ".$turma->id);
                    // o ideal é parar as matriculas dessa turma e emitir um aviso para secretaria de que a turma está dando problema. 
                    //throw new \Exception("Erro ao acessar valor da turma:".$inscricoes->first()->turma->id.' Matrricula:'.$matricula->id .'. Verifique se a turma está com seu valor devidamente atribuído ou se são foi escolhido a parceria no caso de disciplinas gratuítas.', 1);
                }
    		}
    	}

    }
    public static function retornarZero($msg='Valor não disponível no tabela de valores.'){
        $valor = new Valor;
        $valor->valor = 0;
        $valor->parcelas = 1;
        $valor->referencia = $msg;
        return $valor;

    }

    public static function gerar($valor,$parcelas,$referencia='gerado por alguma função'){
        $valor = new Valor;
        $valor->valor = $valor;
        $valor->parcelas = $parcelas;
        $valor->referencia = $referencia;
        return $valor;

    }

    /**
     * Função que retorna array com a serie de dados IPCA dado intervalo
     * 
     * Serve para atualizar o valor de uma dívida ativa, usado no calculo do fator multiplicador
     *
     * @return \Array [AAAAMM] => $valor
     */
    public static function getIPCA(){

        
            $url = 'https://servicodados.ibge.gov.br/api/v3/agregados/6691/periodos/200101-'.date('Ym').'/variaveis/63?localidades=N1[all]'; //IBGE
      
            //$url = 'https://api.bcb.gov.br/dados/serie/bcdata.sgs.433/dados?formato=json'; //BACEN

            //http://ipeadata.gov.br/api/odata4/ValoresSerie(SERCODIGO='PRECOS12_IPCAG12')  IPEA

        //dd($url);

        $ch = curl_init();
        //não exibir cabeçalho
        curl_setopt($ch, CURLOPT_HEADER, false);
        // redirecionar se hover
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // desabilita ssl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //envia a url
        curl_setopt($ch, CURLOPT_URL, $url);
        //executa o curl
        $result = curl_exec($ch);
        //encerra o curl

        $status = curl_getinfo($ch);
        if($status["http_code"]== 400 || $status["http_code"]== 500)
                throw new \Exception("Erro ".$status["http_code"]. " ao acessar URL com os dados IPCA em ValorController:getIPCA-> ".$url, 1);
                
        curl_close($ch);
        $ws = json_decode($result);
        //dd($ws);

        if(isset($ws->erro) || !$ws)
                throw new \Exception("Erro ao processar dados do IPCA. Verifique o status do serviço do IBGE: ".$ws->erro, 1);

        return (array)$ws[0]->resultados[0]->series[0]->serie;  
    }

    
    /**
     * Fator Multiplicador
     * 
     * Dada a série de dados obtidos do periodo do ipca ele calcula o fator multipplicador
     *
     * @param Array $serie
     * @return void
     */
    public static function getFatorMultiplicador(Array $serie){
        $primeiro = reset($serie);
        $fator =  $primeiro/100+1;
        //echo $fator."<br>";
        foreach($serie as $key=>$taxa){
            if($key != key($serie)){    
                $fator = ($taxa/100+1)*$fator;
                //echo $fator."<br>";

            }
        }
        //dd($fator);     
        return $fator;

    }

    /**
     * Undocumented function
     *
     * @param float $valor Valor do boleto
     * @param integer $taxa em porcentagem ex. 1 (%)
     * @param integer $periodo
     * @return void
     */
    public static function getJuros(float $valor, int $periodo) {
           
            $m = ($valor * (0.033333 * $periodo))/100;
            return $m;
    }
     
    public static function jurosComposto($valor, $taxa, $parcelas) {
            $taxa = $taxa / 100;
            $valParcela = $valor * pow((1 + $taxa), $parcelas);
            $valParcela = number_format($valParcela / $parcelas, 2, ",", ".");
     
            return $valParcela;
    }

    public static function getMulta($valor){
        return $valor*0.02;
    }

    


}

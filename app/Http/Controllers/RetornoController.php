<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Retorno;
use App\Models\Boleto;
use Illuminate\Support\Facades\Storage;
ini_set('max_execution_time', 180);
class RetornoController extends Controller
{
	

		public function listarRetornos(){

			chdir( '../storage/app/private/documentos/retornos/' );
			$files = glob("{*.ret}", GLOB_BRACE);
			rsort($files);

			//$files = Storage::files('documentos/retornos');



			$arquivos = collect();

			foreach($files as $file){
				$arquivo = new \stdClass();
				$arquivo->nome = $file;
				try{
					$retorno = new \Adautopro\LaravelBoleto\Cnab\Retorno\Cnab240\Banco\Bb($file);
					$retorno->processar();
					$header = $retorno->getHeader();
					$arquivo->id = $header->numeroSequencialArquivo;
					$arquivo->data = $header->data;
				}
				catch(\Exception $e){
				   		//rename($file, $file.'_ERRO');
				   		$arquivo->status = 'Erro ao ler arquivo';
				}

				$arquivos->push($arquivo);

			}
			$arquivos = $arquivos->sortBy('id');
			//return $arquivos;

			return view('financeiro.retorno.lista')->with('arquivos',$arquivos);
		}
		
		public function listarRetornosProcessados(){
			$retornos = Retorno::orderByDesc('id')->paginate(50);
			return view('financeiro.retorno.lista', compact('retornos'))->with('processado',true);
		}
		public function listarRetornosComErro(){
			chdir( '../storage/app/private/documentos/retornos/' );
			$files = glob("{*.ret_ERRO}", GLOB_BRACE);
			rsort($files);

			$arquivos = collect();

			foreach($files as $file){
				$arquivo = new \stdClass();
				$arquivo->nome = $file;
				try{
					$retorno = new \Adautopro\LaravelBoleto\Cnab\Retorno\Cnab240\Banco\Bb($file);
					$retorno->processar();
					$header = $retorno->getHeader();
					$arquivo->id = $header->numeroSequencialArquivo;
					$arquivo->data = $header->data;
				}
				catch(\Exception $e){
				   		//rename($file, $file.'_ERRO');
				   		$arquivo->status = 'Erro ao ler arquivo';
				}

				$arquivos->push($arquivo);

			}
			$arquivos = $arquivos->sortBy('id');
			//return $arquivos;

			return view('financeiro.retorno.lista')->with('arquivos',$arquivos)->with('erro',true);
		}


		public function processarRetornos(Request $request){ //processava todos arquivos de uam vez
			$arquivos = glob($request->arquivo, GLOB_BRACE);
			//return $arquivos;
			$processamento = array();

			foreach($arquivos as $arquivo){
			   $bd_retorno = Retorno::where('nome_arquivo', 'like', $arquivo)->get();
			   if(count($bd_retorno) == 0){
				   	try{   		
				   		$processamento[] = $this->processarArquivo($arquivo);
				   	} 
				   	catch(\Exception $e){
				   		rename($arquivo, $arquivo.'_ERRO');
						continue;
					}
					rename($arquivo, $arquivo.'_processado');
					$retorno = new Retorno;
			   		$retorno->nome_arquivo = $arquivo;
			   		$retorno->timestamps=false;
			   		$retorno->save();
			   } 
			   else
			   	rename($arquivo, $arquivo.'_processado');
			}
			return redirect(asset('/financeiro/boletos/retorno/processados'));
		}

		public function retornarOriginal($arquivo){
			$arquivo='../storage/app/private/documentos/retornos/'.$arquivo;
			$retorno = new \Adautopro\LaravelBoleto\Cnab\Retorno\Cnab240\Banco\Bb($arquivo);
			$retorno->processar();
			dd($retorno);

		}
		public function analisarArquivo($arquivo){
			$arquivo='../storage/app/private/documentos/retornos/'.$arquivo;
			if(!file_exists($arquivo))
				return "Arquivo ".$arquivo." não encontrado.";
			
			$titulos_baixados=array();
			try{
				$retorno = new \Adautopro\LaravelBoleto\Cnab\Retorno\Cnab240\Banco\Bb($arquivo);
				$retorno->processar();
			}
			catch(\Exception $e){
				   		rename($arquivo, $arquivo.'_ERRO');
				   		return redirect($_SERVER['HTTP_REFERER'])->withErrors([$arquivo.' foi descartado pois apresentou erro ao ser analisado. Faça o upload novamente ou gere um novo arquivo de retorno no BB e tente novamente.']);
					}

			//dd($retorno);
			$header = $retorno->getHeader();
			$retorno_id = $header->numeroSequencialArquivo;
			$retorno_data = $header->dataOcorrencia;
			$detalhes = $retorno->getDetalhes();

			$retorno_bd = Retorno::find($retorno_id);
			if($retorno_bd != null)
				$processado = true;
			else
				$processado = False;
			$total=0;
			$acrescimos=0;
			$taxas=0;
			$descontos=0;
			$liquidado=0;

			foreach($detalhes as $linha){
				
				 // se o começo é o da carteira de boletos
				
					$titulos_baixados[$linha->nossoNumero]['id'] = $linha->nossoNumero;
					$titulos_baixados[$linha->nossoNumero]['data'] = $linha->dataOcorrencia;
					$titulos_baixados[$linha->nossoNumero]['valor'] = 'R$ '.number_format($linha->valorRecebido+$linha->valorTarifa+$linha->valorMulta+$linha->valorMora,2,',','.');
					$boleto = Boleto::find(str_replace('2838669','',$linha->nossoNumero)*1);
					if($boleto != null){
						$titulos_baixados[$linha->nossoNumero]['boleto_status'] = $linha->ocorrenciaTipo.') '.$linha->ocorrenciaDescricao.' '.$linha->error;
					}
					else{
						$titulos_baixados[$linha->nossoNumero]['boleto_status'] = 'Nro. Inválido';
					}

					$total = $total + $linha->valorRecebido+$linha->valorTarifa;
					$liquidado = $liquidado + $linha->valorRecebido+$linha->valorTarifa+$linha->valorDesconto-($linha->valorMulta + $linha->valorMora);
					$acrescimos = $acrescimos + $linha->valorMulta + $linha->valorMora;
					$taxas = $taxas+$linha->valorTarifa;
					$descontos = $descontos+$linha->valorDesconto;
					
						
			}

			return view('financeiro.retorno.titulos')->with('titulos',$titulos_baixados)->with('total',$total)->with('acrescimos',$acrescimos)->with('taxas',$taxas)->with('descontos',$descontos)->with('liquidado',$liquidado)->with('arquivo',$arquivo)->with('processado',$processado)->with('id',$retorno_id);


		}
		public function processarArquivo($arquivo){
			$arquivo='../storage/app/private/documentos/retornos/'.$arquivo;
			$retorno = new \Adautopro\LaravelBoleto\Cnab\Retorno\Cnab240\Banco\Bb($arquivo);
			$retorno->processar();

			$header = $retorno->getHeader();
			$retorno_id = $header->numeroSequencialArquivo;
			$retorno_data = $header->data;
			$detalhes = $retorno->getDetalhes();
			$data = $header->data. ' 00:00:00';
			//verificar se ja foi processado
			$retorno_bd= new Retorno;
			$retorno_bd->id = $retorno_id;
			$retorno_bd->nome_arquivo = $arquivo;
			$retorno_bd->data_ocorrencia = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $data, 'Europe/London');
			$retorno_bd->save();

			foreach($detalhes as $linha){
				
				$boleto= Boleto::find(str_replace('2838669','',$linha->nossoNumero)*1);//procura o boleto no banco
				if(!is_null($boleto)){

					switch($linha->ocorrenciaTipo){
						case 1: //Liquidação
							if($boleto->status == 'cancelar')
								\App\Models\Lancamento::where('boleto',$boleto->id)->update(['status'=>'']);//coloca o status dos lançamentos desse boleto como null ao inves de cancelados
							$boleto->status = 'pago';
							$boleto->pagamento = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $data, 'Europe/London');
							$boleto->pago = $linha->valor;
							$boleto->encargos = $linha->valorMulta + $linha->valorMora + $linha->valorIOF;
							$boleto->descontos = $linha->valorDesconto;
							$boleto->retorno = $retorno_id;
							$boleto->save();							
							LogController::alteracaoBoleto($boleto->id,'Pagamento confirmado, retorno: '.$retorno_id.': '.$linha->ocorrenciaDescricao.' '.$linha->error);
							//MatriculaController::liberarMatriculadoBoleto($boleto);

						break;
						case 3: //Entrada confirmada
							if($boleto->status == 'gravado' || $boleto->status == 'impresso'){
								$boleto->status = 'emitido';
								$boleto->save();
								LogController::alteracaoBoleto($boleto->id,'Boleto registrado, retorno: '.$retorno_id.': '.$linha->ocorrenciaDescricao.' '.$linha->error);
							}
						break;
						case 2://baixa
							if($boleto->status == 'cancelar'){
								$boleto->status = 'cancelado';
								$boleto->save();
								LogController::alteracaoBoleto($boleto->id,'Boleto cancelado, retorno: '.$retorno_id.': '.$linha->ocorrenciaDescricao.' '.$linha->error);
							}

						case 9://rejeição
							if($boleto->status == 'impresso'){
								$boleto->status = 'erro';
								$boleto->save();
							}
							if($boleto->status == 'cancelar'){
								$boleto->status = 'cancelado';
								$boleto->save();
							}
							LogController::alteracaoBoleto($boleto->id,'Boleto Rejeitado: '.$linha->ocorrenciaDescricao);					
							
						break;						
					}				
				}		
			}
			rename($arquivo, $arquivo.'_PROC');
			return redirect(asset('financeiro/boletos/retorno/analisar'.'/'.substr($arquivo,20).'_PROC'))->withErrors([$arquivo.' foi processado com sucesso']);
		}


		public function reProcessarArquivo($arquivo){
			$arquivo='../storage/app/private/documentos/retornos/'.$arquivo;
			$retorno = new \Adautopro\LaravelBoleto\Cnab\Retorno\Cnab240\Banco\Bb($arquivo);
			$retorno->processar();
			
			$header = $retorno->getHeader();
			$retorno_id = $header->numeroSequencialArquivo;
			$retorno_data = $header->data;
			$detalhes = $retorno->getDetalhes();
			$data = $header->data. ' 00:00:00';
			//verificar se ja foi processado
			$retorno_existe = Retorno::find($header->numeroSequencialArquivo);
			if($retorno_existe == null){
				$retorno_bd= new Retorno;
				$retorno_bd->id = $retorno_id;
				$retorno_bd->nome_arquivo = $arquivo;
				$retorno_bd->data_ocorrencia = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $data, 'Europe/London');;
				$retorno_bd->save();
			}	

			foreach($detalhes as $linha){
				//dd($linha);
				$boleto= Boleto::find(str_replace('2838669','',$linha->nossoNumero)*1);//procura o boleto no banco
				if(!is_null($boleto)){
					switch($linha->ocorrenciaTipo){
						case 1: //Liquidação
							if($boleto->status == 'cancelar')
								\App\Models\Lancamento::where('boleto',$boleto->id)->update(['status'=>'']);
							$boleto->status = 'pago';
							$boleto->pagamento = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $data, 'Europe/London');
							$boleto->pago = $linha->valor;
							$boleto->encargos = $linha->valorMulta + $linha->valorMora + $linha->valorIOF;
							$boleto->descontos = $linha->valorDesconto;
							$boleto->retorno = $retorno_id;
							$boleto->save();
							LogController::alteracaoBoleto($boleto->id,'Pagamento confirmado');
							break;
						case 3: //Entrada confirmada
							if($boleto->status == 'gravado' || $boleto->status == 'impresso' || $boleto->status == 'pelosite' ){
								$boleto->status = 'emitido';
								$boleto->save();
								LogController::alteracaoBoleto($boleto->id,'Registro confirmado');
							}
							break;
						case 2://baixa
							if($boleto->status == 'cancelar'){
								$boleto->status = 'cancelado';
								$boleto->save();
								LogController::alteracaoBoleto($boleto->id,'Baixa confirmada');
							}

						case 9://reieição
							if($boleto->status == 'impresso'){
								$boleto->status = 'erro';
								$boleto->save();
								//adicionar pendencia na pessoa
								PessoaDadosAdministrativos::cadastrarUnico($boleto->pessoa,'pendencia','Erro no boleto'.$boleto->id.': '.$linha->ocorrenciaDescricao);
							}
							if($boleto->status == 'cancelar'){
								$boleto->status = 'cancelado';
								$boleto->save();
							}							
							LogController::alteracaoBoleto($boleto->id,'ERRO: '.$linha->ocorrenciaDescricao);
							break;						
					}
					LogController::alteracaoBoleto($boleto->id,'Boleto processado pelo arquivo de retorno: '.$retorno_id.': '.$linha->ocorrenciaDescricao.' '.$linha->error);
				}		
			}
			if(substr($arquivo,-4) != 'PROC'){
				rename($arquivo, $arquivo.'_PROC');
				$arquivo = $arquivo.'_PROC';
			}
			
			return redirect(asset('financeiro/boletos/retorno/analisar'.'/'.substr($arquivo,20)))->withErrors([$arquivo.' foi reprocessado.']);
		}
		
		public function marcarErro($arquivo){
			$arquivo='../storage/app/private/documentos/retornos/'.$arquivo;
			$arquivo = Storage::get($arquivo);

			rename($arquivo, $arquivo.'_ERRO');
			return redirect($_SERVER['HTTP_REFERER'])->withErrors([$arquivo.' foi descartado pois apresentou erro ao ser analisado. Faça um novo upload ou gere outro arquivo de retorno no BB e tente novamente.']);
		}
		public function marcarProcessado($arquivo){
			$arquivo='../storage/app/private/documentos/retornos/'.$arquivo;
			rename($arquivo, $arquivo.'_PROC');
			return redirect(asset('financeiro/boletos/retorno/arquivos'))->withErrors([$arquivo.' foi marcado como processado.']);
		}
}

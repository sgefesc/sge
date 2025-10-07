<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Boleto;
use Illuminate\Support\Facades\Storage;

class RemessaController extends Controller
{
    public function gerarRemessa(){
		$BC = new BoletoController;
		$beneficiario = new \Adautopro\LaravelBoleto\Pessoa([
		    'documento' => '45.361.904/0001-80',
		    'nome'      => 'Fundação Educacional São Carlos',
		    'cep'       => '13560-230',
		    'endereco'  => 'Rua São Sebastiao, 2828, ',
		    'bairro' => ' Vila Nery',
		    'uf'        => 'SP',
		    'cidade'    => 'São Carlos',
		]);
		$remessa = new \Adautopro\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bb(
		    [
		        'agencia'      => env('BB_AGENCIA'),
		        'carteira'     => 17,
		        'conta'        => env('BB_CONTA'),
		        'convenio'     => env('BB_CONVENIO'),
		        'variacaoCarteira' => env('BB_CARTEIRA'),
		        'beneficiario' => $beneficiario,
		    ]
		);
		
		$boletos =Boleto::whereIn('status',['impresso','pelosite','cancelar'])->paginate(300);
		if($boletos->count() == 0)
			return redirect($_SERVER['HTTP_REFERER'])->withErrors(['Nenhum boleto encontrado']);
		foreach($boletos as $boleto){
			try{
				$boleto_completo = $BC->gerarBoleto($boleto);
			}
			catch(\Exception $e){
				NotificacaoController::notificarErro($boleto->pessoa,5);
				continue;
			}
			if($boleto->status == 'cancelar' && isset($boleto_completo))
				$boleto_completo->baixarBoleto();	
			try{
				$remessa->addBoleto($boleto_completo);
			}
			catch(\Exception $e){
				NotificacaoController::notificarErro($boleto->pessoa,6);
				continue;
			}
			$boleto->remessa = intval(date('ymdHi'));
			$boleto->save();		
		}
		if(isset($_GET["page"]))
			$page = $_GET["page"];
		else
			$page = 1;

		$remessa->save( env('STORAGE_HOME').'/documentos/remessas/'.date('YmdHis').'_'.$page.'.rem');
		$arquivo = date('Ymd').'_'.$page.'.rem';
		return view('financeiro.remessa.gerador-paginado',compact('boletos'))->with('arquivo',$arquivo);

	}

	public function listarRemessas(){
		$arquivos = Storage::files('documentos/remessas');
		return view('financeiro.remessa.lista')->with('arquivos',$arquivos);
	}
}

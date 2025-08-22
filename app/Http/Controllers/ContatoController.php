<?php

namespace App\Http\Controllers;

use App\Models\Contato;
use Illuminate\Http\Request;
use Auth;

class ContatoController extends Controller
{
    public function registrar(Request $r){

        switch($r->meio){
            case 'sms': 
                //$sms = $this->enviarSMS($r->mensagem,[$r->pessoa]);
                $this->dispatch(new \App\Models\Jobs\EnviarSMS($r->mensagem,$r->pessoa,Auth::user()->pessoa));
                return response('ok',200);
                break;
            case 'email':
                Mail::send('emails.default', ['username' => $user->username , 'password' => $password], function ($message) use($user){
					$message->from('no-reply@fesc.saocarlos.sp.gov.br', 'Sistema Fesc');
					$message->to($user->email);
					$message->subject('Contato');
					});
              
                break;
            case 'pendencia':
                $pendencia = \App\Models\PessoaDadosAdministrativos::addPendencia($r->pessoa,$r->mensagem);   
                return response($pendencia,200);
                break;

            default:
                $contato = $this->novoContato($r->pessoa,$r->meio,$r->mensagem,Auth::user()->pessoa);
                return response($contato,200);
                break;
        }
            
       
        
    }

    public function novoContato($para,$meio,$mensagem,$por){

        $contato = new Contato;
        $contato->meio = $meio;
        $contato->mensagem = $mensagem;
        $contato->por = $por;
        $contato->para = $para;
        $contato->save();

        return $contato;

    }

    public function enviarSMS(string $mensagem, int $pessoa, int $destinatario){
        
            $pessoa = \App\Models\Pessoa::find($pessoa);
            if(!isset($pessoa->id))
             throw new \Exception('Pessoa não encontrada');
            $pessoa->celular = $pessoa->getCelular();

            if(strlen($pessoa->celular)>5){
                $mensagem=substr(urlencode('FESC Informa: '.$mensagem),0,140);
                $url = 'http://209.133.205.2/painel/api.ashx?action=sendsms&lgn=16997530315&pwd=194996&msg='.$mensagem.'&numbers='.$pessoa->celular;        
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
                if(isset($ws->msg) && $ws->msg == 'SUCESSO'){
                    $contato = $this->novoContato($pessoa->id,'sms',urldecode($mensagem).' numero:'.$pessoa->celular,$destinatario);
                    return $contato;
                }
                else
                    return $ws;
                
            }
           
                   
    }

    public function enviarWhats(){
        
        if(isset($_GET['pessoa'])){
            $pessoa = \App\Models\Pessoa::find($_GET['pessoa']);
            if(isset($pessoa->id)){
                $pessoa->celular =  $pessoa->getCelular();
                $contato = $this->novoContato($pessoa->id,'whatsapp',$_GET['msg'].' numero:'.$pessoa->celular,Auth::user()->pessoa);
                return redirect('https://wa.me/55'.$pessoa->celular.'?text='.$_GET['msg']);
            }
        }
        else
            return response('Pessoa não identificada', 500);
            

    }

    
}

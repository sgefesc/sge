<?php

namespace App\Http\Controllers;

use App\Models\PessoaDadosContato;
use Illuminate\Http\Request;
use Auth;

class PessoaDadosContatoController extends Controller
{
    public static function gravarTelefone($pessoa, $numero = null){
        if(!is_null($numero) 
            && $numero != ' ' 
            && $numero != '' 
            && $numero != '123' 
            && $numero != '123456' 
            && strlen($numero)>=8 
            && strlen($numero)<16){
            //procura pra ver se telefone já existe
            $dado = PessoaDadosContato::where('dado','2')->where('pessoa',$pessoa)->where('valor', $numero)->get();
            //cadastra se nao tiver
            if($dado->count() == 0){
                $telefone = new PessoaDadosContato;
                $telefone->pessoa = $pessoa;
                $telefone->dado = '2';
                $telefone->valor = preg_replace( '/[^0-9]/is', '', $numero);
                $telefone->save();
                return $telefone;

            }
        }
        else {
            return null;
        }

    }
    public static function gravarEndereco(int $pessoa, $logradouro=null, $numero=null, $complemento=null, $bairro=null, $cidade=null, $uf=null, $cep=null){
        if(is_null($cep)){
            return null;
        }
        else{
            $endereco_atual = PessoaDadosContato::where('pessoa',$pessoa)->where('dado',6)->get();
        }

    }

    public static function gravarEmail(int $pessoa, string $email){
        
        $address = explode('@', $email);
        if(count($address)!=2)
            return false;
        else{
            list($username, $domain) = $address;
            if (checkdnsrr($domain, 'MX')) {
                $contato = new PessoaDadosContato;
                $contato->pessoa = $pessoa;
                $contato->dado = '1';
                $contato->valor = $email;
                $contato->save();
                return $contato;
            }
            else {
                return false;
            }
        }

        

    }
}

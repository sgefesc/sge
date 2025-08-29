<?php
namespace App\classes;

use Illuminate\Support\Facades\Storage;

Class Arquivo {

	public static function download(string $tipo,  int $arquivo){
            switch($tipo){
                  case 'matricula':
                        $arquivo = 'documentos/matriculas/termos/'.$arquivo.'.pdf';
                        break;
                  case 'atestado':
                        $arquivo = 'documentos/atestados/'.$arquivo.'.pdf';
                        break;
                  case 'bolsa':
                        $arquivo = 'documentos/bolsas/'.$arquivo.'.pdf';
                        break;
                  case 'parecer':
                        $arquivo = 'documentos/bolsas/parecer-'.$arquivo.'.pdf';
                        break;
                  case 'retorno':
                        $arquivo = 'documentos/retornos/'.$arquivo;
                        break;
                  default:
                        return "Tipo de arquivo inválido.";
            }
            if(!Storage::exists('/'.$arquivo)){
                  return "Arquivo ".$arquivo. ' não encontrado.';
            }

            return Storage::download($arquivo);

            /*
	  //require $arquivo teste;
            $arquivo = str_replace('-.-','/',$arquivo);
            //dd(substr($arquivo,1));
            if(substr($arquivo,0,1) =='/')
                  $arquivo = substr($arquivo,1);
            if(file_exists($arquivo)){
                  //dd($arquivo);
                  header("Content-Type: application/force-download");
                  header("Content-Type: application/octet-stream;");
                  header("Content-Length:".filesize($arquivo));
                  header("Content-disposition: attachment; filename=".$arquivo);
                  header("Pragma: no-cache");
                  header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
                  header("Expires: 0");
                  readfile($arquivo);
                  flush();
            }
            else
                  return "Arquivo ".$arquivo. ' não encontrado.';*/
      
	}
      public static function show($tipo, $arquivo){

           if(!Storage::exists($arquivo)){
                  return "Arquivo ".$arquivo. ' não encontrado.';
            }
            return Storage::response($arquivo);
                
          
      }

      public static function delete (string $uri){
            if(Storage::exists($uri)){
                  try{
                        Storage::delete($uri);
                  }
                  catch(\Exception $e){
                        dd("Erro ao apagar arquivo ".$uri." - ".$e->getMessage());
                  }
                  
                  return true;
            }
            else
                  return false;
      }

      public function getPath($tipo){
            switch($tipo){
                  case 'matricula':
                        return 'documentos/matriculas/termos';
                        break;
                  case 'atestado':
                        return 'documentos/atestados';
                        break;
                  case 'bolsa':
                        return 'documentos/bolsas/';
                        break;
                  case 'parecer':
                        return 'documentos/bolsas/parecer';
                        break;
                  case 'retorno':
                        return 'documentos/retornos';
                        break;
                  default:
                        return null;
            }
      }






}


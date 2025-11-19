<?php
namespace App\classes;

use Illuminate\Support\Facades\Storage;

Class Arquivo {

      public static function getPath($tipo,$arquivo){
            switch($tipo){
                  case 'matricula':
                        return 'documentos/matriculas/termos/'.$arquivo.'.pdf';
                        break;
                  case 'atestado':
                        return 'documentos/atestados/'.$arquivo.'.pdf';
                        break;
                  case 'requerimento':
                  case 'bolsa':
                        return 'documentos/bolsas/requerimentos/'.$arquivo.'.pdf';
                        break;
                  case 'parecer':
                        return 'documentos/bolsas/pareceres/'.$arquivo.'.pdf';
                        break;
                  case 'retorno':
                        return 'documentos/retornos/'.$arquivo;
                        break;
                  case 'remessa':
                        return 'documentos/remessas/'.$arquivo;
                        break;
                  case 'foto':
                        return 'documentos/fotos_perfil/'.$arquivo.'.jpg';
                        break;
                  default:
                        return "/".$arquivo.'.pdf';
            }         
      }

	public static function download(string $tipo, $arquivo){
            $arquivo = Arquivo::getPath($tipo, $arquivo);
          
            if(!Storage::exists('/'.$arquivo)){
                  return "Arquivo ".$arquivo. ' não encontrado.';
            }

            return Storage::download($arquivo);
      
	}



      public static function show($tipo, $arquivo){
            $arquivo = Arquivo::getPath($tipo, $arquivo);

           if(!Storage::exists($arquivo)){
                  return "Arquivo ".$arquivo.' não encontrado.';
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






}


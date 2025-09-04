<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function enviarDocumentosIndividuais(Request $request){
        $request->validate([
            'documento' => 'required|file|mimes:pdf|max:2048', // Validação para arquivos PDF com tamanho máximo de 2MB
            'tipo' => 'required|string',
            'identificador' => 'required|string'
        ]);
        $sucesso = array();
        

        // Fazendo upload do arquivo.
        if($request->hasFile('documento')){ 
            try{
                $path = $request->file('documento')->storeAs('documentos/'.$request->tipo, $request->identificador.'.pdf');
                $sucesso[] = $path;
            }
            catch(\Exception $e){
                return back()->withErrors(['Erro ao enviar o arquivo: '.$e->getMessage()]);
            }
            
        }

        return back()->with('success', 'Documentos enviados com sucesso')->with('files', $sucesso);


    }
    public function enviarDocumentosSecretaria(Request $request){
        $request->validate([
            'arquivos' => 'required|file|mimes:pdf|max:2048', // Validação para arquivos PDF com tamanho máximo de 2MB
        ],
        [
            'arquivos.required' => 'Selecione ao menos um arquivo para envio.',
            'arquivos.mimes' => 'Apenas arquivos em PDF são permitidos.',
            'arquivos.max' => 'Tamanho máximo permitido para envio é 2MB.'
        ]);
        $sucesso = array();
        
        $arquivos = $request->file('arquivos');
        // Fazendo upload do arquivo.
        if($request->hasFile('arquivos')){ 
            foreach($arquivos as $arquivo){
                switch (substr($arquivo->getClientOriginalName(), 5,2)) {
                    case 'MT':
                        $pasta = 'matriculas/termos';
                        break;

                    case 'CM':
                        $pasta = 'matriculas/cancelamentos';
                        break;
                
                    case 'CI':
                        $pasta = 'inscricoes/cancelamentos';
                        break;                
                
                    case 'AM':
                        $pasta = 'atestados';
                        break;
                        
                    case 'RD':
                    case 'RQ':
                        $pasta = 'bolsas/requerimentos';
                        break;
                        
                    case 'PA':
                        $pasta = 'bolsas/pareceres';
                        break;
                        
                    case 'TR':
                        $pasta = 'inscricoes/transferencias';
                        break;

                    case 'JA':
                        $pasta = 'justificativas/ausencias';
                        break;
                        
                    default :
                        $msg[$arquivo->getClientOriginalName()] = 'O arquivo "'.$arquivo->getClientOriginalName().'" não segue o padrão de nomenclatura FESC_XX----.pdf, verifique o nome e envie novamente.';
                        break;
                }
                if (!empty($arquivo)) {
                    try{
                        $path = $arquivo->storeAs('documentos/'.$pasta, preg_replace( '/[^0-9]/is', '', $arquivo->getClientOriginalName()).'.pdf'); 
                        $sucesso[] = $path;
                    }
                    catch(\Exception $e){
                        return back()->withErrors(['Erro ao enviar o arquivo '.$arquivo->getClientOriginalName().' - '.$e->getMessage()]);
                    }
                }
            }
            
        }
        

        return back()->with('success', 'Documentos enviados com sucesso')->with('files', $sucesso);
    }


      /**
     * Execução de upload de cancelamento
     */
    public function uploadCancelamentoMatricula(Request $r){
        $arquivos = $r->file('arquivos');
            foreach($arquivos as $arquivo){
                if (!empty($arquivo)) 
                    $arquivo->move('../../storage/app/documentos/matriculas/cancelamentos', preg_replace( '/[^0-9]/is', '', $arquivo->getClientOriginalName()).'.pdf');    
            }
        return redirect(asset('secretaria/atender'))->withErrors(['Enviados'.count($arquivos).' arquivos.']);
        
    }

    /**
     * Execução de upload de termo
     */
    public function uploadTermo(Request $r){
        $r->validate([
            'arquivo' => 'required|mimes:pdf|max:4096'
        ],
        [
            'arquivo.required' => 'Selecione um arquivo para envio.',
            'arquivo.mimes' => 'Apenas arquivos em PDF são permitidos.',
            'arquivo.max' => 'Tamanho máximo permitido para envio é 4MB.'
        ]);
        $arquivo = $r->file('arquivo');                  
            if (!empty($arquivo)) 
                try{
                    $path = $r->file('arquivo')->storeAs('documentos/matriculas/termos', preg_replace( '/[^0-9]/is', '', $r->matricula).'.pdf'); 
                }
                catch(\Exception $e){
                    return redirect($_SERVER['HTTP_REFERER'])->withErrors(['Erro ao enviar arquivo '.$arquivo->getClientOriginalName().' - '.$e->getMessage()]);
                }      

            return redirect(asset('secretaria/atender'));
    }

    /**
     * Execução de upload em lote
     */
    public function uploadTermosLote(Request $r){
        $r->validate([
            'arquivos' => 'required|mimes:pdf|max:4096'
        ],
        [
            'arquivos.required' => 'Selecione ao menos um arquivo para envio.',
            'arquivos.mimes' => 'Apenas arquivos em PDF são permitidos.',
            'arquivos.max' => 'Tamanho máximo permitido para envio é 4MB.'
        ]);
        $arquivos = $r->file('arquivos');
        foreach($arquivos as $arquivo){
            //dd($arquivo);
            if (!empty($arquivo)) 
                try{
                    $path = $r->file('arquivos')->storeAs('documentos/matriculas/termos', preg_replace( '/[^0-9]/is', '', $arquivo->getClientOriginalName()).'.pdf'); 
                }
                catch(\Exception $e){
                    return redirect($_SERVER['HTTP_REFERER'])->withErrors(['Erro ao enviar arquivo '.$arquivo->getClientOriginalName().' - '.$e->getMessage()]);
                }
                $arquivo->move('../../storage/app/documentos/matriculas/termos', preg_replace( '/[^0-9]/is', '', $arquivo->getClientOriginalName()).'.pdf');
        }
        return redirect(asset('secretaria/matricula/upload-termo-lote'))->withErrors(['Enviados'.count($arquivos).' arquivos.']);
    }


    /**
     * Execução de upload de parecer de bolsa
     */
    public function uploadParecerExec(Request $r){
    $r->validate([
        'arquivo' => 'required|file|mimes:pdf|max:2048', // Validação para arquivos PDF com tamanho máximo de 2MB
        'bolsa' => 'required|integer'
    ],
    [
        'arquivo.required' => 'Selecione um arquivo para envio.',
        'arquivo.mimes' => 'Apenas arquivos em PDF são permitidos.',
        'arquivo.max' => 'Tamanho máximo permitido para envio é 2MB.'
    ]);
    $arquivo = $r->file('arquivo');
        
            if (!empty($arquivo)) {

                try{
                    $path = $arquivo->storeAs('documentos/bolsas/pareceres', preg_replace( '/[^0-9]/is', '', $r->bolsa).'.pdf'); 
                }
                catch(\Exception $e){
                    return redirect('/bolsas/analisar'.'/'.$r->bolsa)->withErrors(['Erro ao enviar arquivo '.$arquivo->getClientOriginalName().' - '.$e->getMessage()]);
                }
                
            }

        return redirect('/bolsas/analisar'.'/'.$r->bolsa)->with('success','Arquivo enviado com sucesso.');
    }



    public function uploadBolsaExec(Request $r){
        $r->validate([
            'arquivo' => 'required|file|mimes:pdf|max:4096', // Validação para arquivos PDF com tamanho máximo de 2MB
            'matricula' => 'required|integer'
        ],
        [
            'arquivo.required' => 'Selecione um arquivo para envio.',
            'arquivo.mimes' => 'Apenas arquivos em PDF são permitidos.',
            'arquivo.max' => 'Tamanho máximo permitido para envio é 2MB.'
        ]);
        $arquivo = $r->file('arquivo');      
            if (!empty($arquivo)) {
                try{
                    $path = $arquivo->storeAs('documentos/bolsas/requerimentos', preg_replace( '/[^0-9]/is', '', $r->matricula).'.pdf'); 
                }
                catch(\Exception $e){
                    return redirect($_SERVER['HTTP_REFERER'])->withErrors(['Erro ao enviar arquivo '.$arquivo->getClientOriginalName().' - '.$e->getMessage()]);
                }
            }
            return redirect(asset('secretaria/atender'));
    } 

    /**
     * Upload e análise de arquivos de retorno bancário
     */
    public function uploadRetornos(Request $r){
        $r->validate([
            'arquivos' => 'required|max:4096', // Validação para arquivos PDF com tamanho máximo de 4MB
        ],
        [
            'arquivos.required' => 'Selecione um arquivo para envio.',
            'arquivos.max' => 'Tamanho máximo permitido para envio é 4MB.'
        ]);
		$arquivos = $r->arquivos;
		foreach($arquivos as $arquivo){
			if (!empty($arquivo)) {
                try{
                    $path = $arquivo->storeAs('documentos/retornos', $arquivo->getClientOriginalName());
                    $msg['success'] = 'Arquivo '.$arquivo->getClientOriginalName().' enviado com sucesso.'; 
                }
                catch(\Exception $e){
                    $msg = array();
                    return redirect($_SERVER['HTTP_REFERER'])->withErrors(['Erro ao enviar arquivo '.$arquivo->getClientOriginalName().' - '.$e->getMessage()]);
                }
				//$arquivo->move('documentos/retornos',$arquivo->getClientOriginalName());
			}

		}
		return redirect(asset('financeiro/boletos/retorno/arquivos'))->with('success',$msg['success']);

	}
}

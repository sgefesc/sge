<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pessoa;
use App\Models\Atestado;
use App\Jobs\EnviarEmailContatoJob;
use App\Jobs\EnviarEmailVencimentoAtestado;
use App\Jobs\EnviarWhatsAppContatoJob;


class Contato extends Model
{
    #id, para, por, meio, mensagem, data, status
    public $timestamps = false;

    public function getPessoa() {
        return $this->belongsTo(Pessoa::class, 'para');
    }

    public function enviar(){
        $contatos = Contato::where('status','aguardando')->get();
        foreach($contatos as $contato){
            switch($contato->meio){
                case 'email':
                    // Lógica para enviar e-mail
                    EnviarEmailContatoJob::dispatch($contato);
                    
                    break;
                case 'whatsapp':
                    // Lógica para enviar WhatsApp
                    break;
            }
        }
    }

    public static function enviarContatoVencimentoAtestado(Atestado $atestado) {
        $pessoa = Pessoa::withTrashed()->find($atestado->pessoa);
        if (!$pessoa) 
            return false;
        $msg = 'Atenção: seu atestado médico cod. '.$atestado->id.' vencerá em breve. Por favor, providencie a renovação para continuar realizando suas atividades normalmente.';
        $contato = verificarSeRegistrado($atestado->pessoa, 'email', $msg);
        if (!$contato) {     
            $contato = Contato::registrar($atestado->pessoa, $msg, 'email',0,'enviado' );
            EnviarEmailVencimentoAtestado::dispatch($contato);
        }

        


        Contato::registrar($atestado->pessoa, $msg, 'whatsapp');

        // Lógica para enviar notificação ao contato sobre o vencimento do atestado
        // Pode ser um e-mail, SMS, ou outra forma de comunicação
    }

    public static function enviarContatoAtestadoVencido(Atestado $atestado) {
        $msg = 'Atenção: O atestado médico cod. '.$atestado->id.' está vencido. Envie um novo atestado para poder continuar realizando suas atividades.';
        
    }

    public static function registrar(int $pessoa_id, string $mensagem, string $meio, int $por = 0, string $status = 'aguardando') {
        $pessoa = Pessoa::withTrashed()->find($pessoa_id);
        if ($pessoa) {
            $contato = verificarSeRegistrado($pessoa_id, $meio, $mensagem);
            if ($contato ==  null) {
                $contato = new Contato();
                $contato->para = $pessoa_id;
                $contato->por = $por; // 0 = Sistema
                $contato->meio = $meio;
                $contato->mensagem = $mensagem;
                $contato->data = date('Y-m-d H:i:s');
                $contato->status = $staus;
                $contato->save();
            }
        }
    }

    public static function verificarSeRegistrado($pessoa,$meio, $mensagem){
        $query=Contato::where('para',$pessoa)
            ->where('meio','like',$meio)
            ->where('mensagem','like',$mensagem)
            ->first();
        return $query;
    }


}

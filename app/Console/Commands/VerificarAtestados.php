<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Atestado;
use App\Models\AtestadoLog;
use App\Models\Contato;
use App\Models\PessoaDadosAdministrativos;

class VerificarAtestados extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan app:verificar-atestados
     *
     * @var string
     */
    protected $signature = 'app:verificar-atestados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('Verificando atestados...');
        $hoje = Carbon::now();
        $modificados = 0;
        // pega atestados de saúde com mais de 12 meses
        $atestados = \App\Models\Atestado::where('tipo','saude')->where('status','restrito')->whereDate('emissao','<',(clone $hoje)->subMonths(12))->get();
		foreach($atestados as $atestado){
            $atestado->update([
                'status' => 'vencido'
            ]); 

            $log = new AtestadoLog;
            $log->atestado = $atestado->id;
            $log->evento = 'Procedimento automatizado alteração de status por vencimento.';
            $log->pessoa = 0;
            $log->data = Carbon::now();
            $log->save();
            $modificados++;
            $this->comment('Atestado ID '.$atestado->id.' da pessoa ID '.$atestado->pessoa.' está vencido.');



            // verifica se tem novo atestado enviado, senão lança pendencia
            $atestado_ativo = Atestado::whereIn('status',['aprovado','restrito'])->where('pessoa',$atestado->pessoa)->where('tipo','saude')->first();      
            if($atestado_ativo == null){
                PessoaDadosAdministrativos::lancarPendencia($atestado->pessoa,'Atestado de saúde vencido e nenhum novo atestado enviado.');
                //Pendencia nas natriculas?
                $inscricoes = \App\Models\Inscricao::where('pessoa',$atestado->pessoa)->where('status','regular')->get();
                foreach($inscricoes as $inscricao){
                    $requisito =  \App\Models\CursoRequisito::where('curso',$inscricao->turma)->where('para_tipo','turma')->whereIn('requisito',[18,27])->first();
                    if($requisito){
                        $inscricao->update(['status'=>'pendente']);
                        \App\Models\InscricaoLog::registrar($inscricao->id,0,'Inscrição pendenciada automaticamente por falta de atestado de saúde válido.');
                        $this->warn('Inscrição ID '.$inscricao->id.' da pessoa ID '.$atestado->pessoa.' foi pendenciada por falta de atestado de saúde.');
                    }          
                }
                Contato::enviarContatoAtestadoVencido($atestado);
                $this->warn('Pessoa '.$atestado->pessoa.' não possui atestado ativo. Pendência lançada.');
            }
            else
                $this->info('Pessoa '.$atestado->pessoa.' possui atestado ativo. Nenhuma pendência lançada.');


		}//end foreach validacao 12 meses

        //pegar atestados com mais de 6 meses
        $atestados = \App\Models\Atestado::where('tipo','saude')->where('status','aprovado')->whereDate('emissao','<',(clone $hoje)->subMonths(6))->get();
        foreach($atestados as $atestado){
            $atestado->update([
                'status' => 'restrito'
            ]); 

            $inscricoes = \App\Models\Inscricao::where('pessoa',$atestado->pessoa)->where('status','regular')->get();
            foreach($inscricoes as $inscricao){
                $requisito =  \App\Models\CursoRequisito::where('curso',$inscricao->turma)->where('para_tipo','turma')->where('requisito',27)->first();
                if($requisito){
                    $inscricao->update(['status'=>'pendente']);
                    \App\Models\InscricaoLog::registrar($inscricao->id,0,'Inscrição pendenciada automaticamente por falta de atestado de saúde válido.');
                    $this->warn('Inscrição ID '.$inscricao->id.' da pessoa ID '.$atestado->pessoa.' foi pendenciada por falta de atestado de saúde.');
                }          
            }

        } //end foreach valida atestados 6 meses
        

        //pegar atestados anuais  que vencerão em 1 mês
        $atestados_anuais_proximo_vencimento = \App\Models\Atestado::where('tipo','saude')->whereIn('status',['aprovado','restrito'])->whereDate('emissao','<',(clone $hoje)->subMonths(11))->get();
		foreach($atestados_anuais_proximo_vencimento as $atestado){
             $atestado_ativo = Atestado::whereIn('status',['aprovado','restrito'])->where('pessoa',$atestado->pessoa)->where('tipo','saude')->whereDate('emissao','>',$atestado->emissao)->first();
             if(is_null($atestado_ativo)){
                // enviar notificação para o usuário que o atestado vencerá em 1 mês.
                Contato::enviarContatoVencimentoAtestado($atestado);
                $this->info('Atestado ID '.$atestado->id.' da pessoa ID '.$atestado->pessoa.' está próximo ao vencimento. Enviando notificação.');

             }  
            
        }

        //pegar atestados semestrais que vencerão em 1 mês
        $atestados_semestrais_proximo_vencimento = \App\Models\Atestado::where('tipo','saude')->where('status','aprovado')->whereDate('emissao','<',(clone $hoje)->subMonths(5))->get();
		foreach($atestados_semestrais_proximo_vencimento as $atestado){
            //tem algum outro?
            $atestado_ativo = Atestado::whereIn('status',['aprovado','restrito'])->where('pessoa',$atestado->pessoa)->where('tipo','saude')->whereDate('emissao','>',$atestado->emissao)->first();
             if(is_null($atestado_ativo)){
                //pega as inscrições que precisam de atestado de saúde
                $inscricoes = \App\Models\Inscricao::where('pessoa',$atestado->pessoa)->where('status','regular')->get();
                foreach($inscricoes as $inscricao){
                    $requisito =  \App\Models\CursoRequisito::where('curso',$inscricao->turma)->where('para_tipo','turma')->where('requisito',27)->first();
                    if($requisito){
                        // enviar notificação para o usuário que o atestado vencerá em 1 mês.
                        Contato::enviarContatoVencimentoAtestado($atestado);
                        $this->info('Atestado ID '.$atestado->id.' da pessoa ID '.$atestado->pessoa.' está próximo ao vencimento. Enviando notificação.');             
                    }          

                }

             }
        }
        
        $this->line($atestados->count().' atestados vencidos '.$modificados.' foram alterados.');
        $this->line($atestados_anuais_proximo_vencimento->count().' atestados anuais próximos ao vencimento foram notificados.');
        $this->line($atestados_anuais_proximo_vencimento->count().' atestados semestrais próximos ao vencimento foram notificados.');

    }
}

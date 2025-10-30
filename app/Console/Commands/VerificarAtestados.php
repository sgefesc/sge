<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Atestado;
use App\Models\AtestadoLog;
use App\Models\Contato;

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
        $this->info('Verificando atestados...');
        $hoje = Carbon::now();
        $modificados = 0;
        $atestados = \App\Models\Atestado::where('tipo','saude')->where('status','aprovado')->whereDate('emissao','<',(clone $hoje)->subMonths(12))->get();
        //dd((clone $hoje)->subMonths(12));
		foreach($atestados as $atestado){
            $atestado->update([
                'status' => 'vencido'
            ]);       
            /*
            $atestado->status = 'vencido';
            $atestado->save();*/

            $log = new AtestadoLog;
            $log->atestado = $atestado->id;
            $log->evento = 'Procedimento automatizado de validação';
            $log->pessoa = 0;
            $log->data = Carbon::now();
            $log->save();
            $modificados++;
		}

        //pegar atestados que vencerão em 1 mês
         $atestados = \App\Models\Atestado::where('tipo','saude')->where('status','aprovado')->whereDate('emissao','<',(clone $hoje)->subMonths(11))->get();

        
		foreach($atestados as $atestado){
            // enviar notificação para o usuário que o atestado vencerá em 1 mês.
            Contato::enviarContatoVencimentoAtestado($atestado);

        }
        
        $this->info($atestados->count().' atestados vencidos '.$modificados.' foram alterados.');
    }
}

<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Mail\EnviarAvisoVencimentoAtestado;
use App\Models\Contato;

class EnviarEmailVencimentoAtestado implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(Contato $contato)
    {
        $this->contato = $contato;
        $pessoa = Pessoa::find($contato->para);
        if(!$pessoa)
            $contato->update(['status'=>'falha']);

        $this->email = $pessoa->getEmail();


    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
            Mail::to($this->email)->send(new EnviarAvisoVencimentoAtestado($this->contato->mensagem));
        }
        catch(\Exception $e){
            $this->contato->update(['status'=>'falha']);

        }

    }
}

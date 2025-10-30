<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnviarEmailContato implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    //__construct($parametro1, $parametro2, ...)
    public function __construct($email, $mensagem)
    {
        // criar as variáveis necessárias para o envio do e-mail
        // $this->variavel1 = $parametro1;
        // $this->variavel2 = $parametro2;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // faz a execução de fato usando as variáveis daqui
        // classe::método($this->variavel1, $this->variavel2);
        Mail::to($this->email)->send(new EnviarRelatorioMail($this->dados));
    }
}
//na classe que chama o job
//EnviarEmailContato::dispatch($parametro1, $parametro2, ...);
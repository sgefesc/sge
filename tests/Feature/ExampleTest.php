<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use \App\Jobs\EnviarEmailVencimentoAtestado;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    
    
    /**
     * Teste para saber se ele identifica conflito de turmas
     */
    public function test_conflito_turmas(): void
    {
        //2901 ter, qui das 08:00 as 08:45
        //2933 ter,  das 07:00 as 08:30
        $value = \App\Models\Inscricao::verificarConflitoTurmas([2901], 2933);
        $this->assertEquals(true, $value);
    }


    /**
     * teste para saber se ele identifica turmas sem conflito
     */
    public function test_turmas_nao_conflitantes(): void
    {
        //2901 ter, qui das 08:00 as 08:45
        //2875 ter, qui  das 16:30 as 17:15
        $value = \App\Models\Inscricao::verificarConflitoTurmas([2901], 2875);
        $this->assertNull($value);
    }

    /**
     * Testar o envio de email
     */
    public function test_Criar_Job_Envio_Contato_Atestado_Vencido(): void
    {   
        // 1. Forçar o uso do SMTP (Mailtrap) neste teste
        // Isso ignora a trava do phpunit.xml
        //Config::set('mail.default', 'smtp');
        Config::set('queue.default', 'database');

        $contato = new \App\Models\Contato();
        $contato->destinatario = 19511;
        $contato->remetente  = 19511;
        $contato->meio = "email";
        $contato->mensagem = "Seu atestado vencerá em breve. Por favor, envie um novo atestado para evitar interrupções.";
        $contato->status = "aguardando";
        $contato->data=date('Y-m-d H:i:s');
        $contato->save();
        \App\Jobs\EnviarEmailVencimentoAtestado::dispatch($contato);

       $this->assertDatabaseHas('jobs', [
            'queue' => 'default', // Ou o nome da sua fila
            'attempts' => 0
        ]);

        // Opcional: Verificar se o payload contém o nome da classe do Job
        // Isso garante que é o job certo e não outro perdido.
        $jobNoBanco = \DB::table('jobs')->first();
        $this->assertStringContainsString(
            'EnviarEmailVencimentoAtestado', 
            $jobNoBanco->payload
        );

    }

    public function test_Executando_Job_Envio_Contato_Atestado_Vencido(): void
    {   
        // 1. Forçar o uso do SMTP (Mailtrap) neste teste
        // Isso ignora a trava do phpunit.xml
        //Config::set('mail.default', 'smtp');
        Config::set('queue.default', 'database');

        $contato = new \App\Models\Contato();
        $contato->destinatario = 19511;
        $contato->remetente  = 19511;
        $contato->meio = "email";
        $contato->mensagem = "Seu atestado vencerá em breve. Por favor, envie um novo atestado para evitar interrupções.";
        $contato->status = "aguardando";
        $contato->data=date('Y-m-d H:i:s');
        $contato->save();

        $job = new \App\Jobs\EnviarEmailVencimentoAtestado($contato);
        $job->handle();

       $this->assertDatabaseHas('contatos', [
            'id' => $contato->id,
            'status' => 'enviado'
        ]);

    }
}

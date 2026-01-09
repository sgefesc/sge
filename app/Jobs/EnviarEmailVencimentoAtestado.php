<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable; // Adicionado
use Illuminate\Queue\InteractsWithQueue;    // Adicionado
use Illuminate\Queue\SerializesModels;      // Adicionado (MUITO IMPORTANTE)
use App\Mail\EnviarAvisoVencimentoAtestado;
use App\Models\Contato;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Mail;
use Exception;

class EnviarEmailVencimentoAtestado implements ShouldQueue
{
    // Traits padrão de um Job Laravel
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * Usamos Constructor Promotion para definir a propriedade automaticamente.
     */
    public function __construct(
        public Contato $contato
    ) {
       
    }

    /**
     * Execute the job.
     * A lógica pesada fica AQUI.
     */
    public function handle(): void
    {
        // 1. Verifica se o contato ainda existe ou se foi cancelado antes de processar
        if (!$this->contato) {
            return;
        }

        try {
            // 2. Busca a pessoa (Agora isso roda no background, não trava o usuário)
            $pessoa = Pessoa::find($this->contato->destinatario);

            // 3. Validação da pessoa
            if (!$pessoa) {
                $this->contato->update([
                    'status' => 'falha',
                    'mensagem' => 'Pessoa não encontrada para o ID: ' . $this->contato->destinatario
                ]);
                return; // Encerra o job
            }

            $email = $pessoa->getEmail();

            // 4. Validação do e-mail
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->contato->update([
                    'status' => 'falha',
                    'mensagem' => 'E-mail não cadastrado ou inválido.'
                ]);
                return;
            }

            // 5. Envio
            Mail::to($email)->send(new EnviarAvisoVencimentoAtestado($this->contato)); 
            $this->contato->update(['status' => 'enviado']);

        } catch (Exception $e) {
            // 6. Captura de erro genérico
            // DICA: O Laravel 12 já joga falhas no log automaticamente e na tabela failed_jobs,
            // mas manter o update no seu model é útil para sua regra de negócio.
            
            $this->contato->update([
                'status' => 'falha',
                'mensagem' => substr($e->getMessage(), 0, 250) // Limita tamanho para não estourar coluna
            ]);
            
            // Opcional: Relançar o erro para que o Laravel saiba que o Job falhou oficialmente
            // throw $e; 
        }
    }
}
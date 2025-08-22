<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class teste extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:teste';

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
        $this->info('Teste de comando executado com sucesso!'); //verde
        // Aqui você pode adicionar a lógica que deseja testar
        // Por exemplo, interagir com o banco de dados, chamar outros serviços, etc.
        
        // Exemplo simples:
        $this->line('Este é um comando de teste.'); //branco
        $this->comment('Você pode adicionar mais lógica aqui.'); //amarelo
        $this->warn('Cuidado com os comandos que você executa!'); //amarelo
        $this->info('Este comando foi criado para fins de teste.');
        $this->line('Você pode usar diferentes métodos para exibir mensagens.'); //branco
        $this->error('Este é um erro de teste.'); //vermelho
        
        // Se precisar de mais lógica, adicione aqui
    }
}

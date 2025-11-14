<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

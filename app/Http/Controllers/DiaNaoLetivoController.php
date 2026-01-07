<?php

namespace App\Http\Controllers;

use App\Models\DiaNaoLetivo;
use Illuminate\Http\Request;

class DiaNaoLetivoController extends Controller
{
    public static function eLetivo(\DateTime $data){
        //$data2 = \DateTime::createFromFormat('d/m/Y','08/07/2020');
        $dia = DiaNaoLetivo::whereDate('data',$data->format('Y-m-d'))->first();   
        
        if(is_null($dia))
            return true;
        else
            return false;
    }

    public function addRecesso(string $inicio, string $termino){
      
        $inicio = \DateTime::createFromFormat('d/m/Y',$inicio);
        $termino = \DateTime::createFromFormat('d/m/Y',$termino);
        for($i=$inicio; $i<=$termino; $i->add(new \DateInterval('P1D'))){
            $dia = DiaNaoLetivo::where('data',$i->format('Y-m-d'))->first();
            if(is_null($dia)){
                $novo_dia = new DiaNaoLetivo;
                $novo_dia->data = $i->format('Y-m-d');
                $novo_dia->descricao = 'Recesso Escolar';
                $novo_dia->save();
            }

        }

        return "Recesso inserido com sucesso. Desde ".$inicio->format('d/m/Y')." até ".$termino->format('d/m/Y');
    }

    public function ViewAddRecesso(){

        return $this->addRecesso('06/07/2025','20/07/2025');
    }

    public function cadastroAnual($ano = 2026){
        $feriados_nacionais = \App\classes\Data::diasFeriados($ano);
        $feriados_estaduais =  ['Revolução Constitucionalista' => $ano.'-'.'07-09'];
        $feriados_municipais = ['N.S. da Babilônia' => $ano.'-'.'08-15', 
                                'Aniversário da cidade' => $ano.'-'.'11-04'];
                            
        $pontos_facultativos = [
                              


            // --- FEVEREIRO ---
            'Carnaval (Sábado)' => '2026-02-14',
            'Carnaval (Domingo)' => '2026-02-15',
            'Ponto facultativo - Carnaval' => '2026-02-16',
            'Ponto facultativo - Carnaval' => '2026-02-17',
            'Ponto facultativo - Carnaval' => '2026-02-18',
            'Quarta-feira de Cinzas (Retorno após 12h)' => '2026-02-18',
            'Reunião do CEU' => '2026-02-26',

            // --- ABRIL ---
            'Ponto facultativo' => '2026-04-02',
            'Sexta-Feira Santa - Feriado Nacional' => '2026-04-03',
            'Ponto facultativo - Emenda Tiradentes' => '2026-04-20',
            'Tiradentes - Feriado Nacional' => '2026-04-21',

            // --- MAIO ---
            'Dia do Trabalhador - Feriado Nacional' => '2026-05-01',
            'Emenda de Feriado (Sábado em Amarelo)' => '2026-05-02',

            // --- JUNHO ---
            'Corpus Christi - Feriado Nacional' => '2026-06-04',
            'Ponto facultativo - Emenda' => '2026-06-05',
            'Emenda de Feriado' => '2026-06-06',
            'Festa Junina (Não letivo)' => '2026-06-20',
           
            // --- JULHO ---
            'Revolução Constitucionalista - Feriado Estadual' => '2026-07-09',
            'Ponto facultativo - Emenda' => '2026-07-10',
            'Ponto facultativo - Emenda' => '2026-07-11',
            'Recesso Escolar' => '2026-07-13',
            'Recesso Escolar' => '2026-07-14',
            'Recesso Escolar' => '2026-07-15',
            'Recesso Escolar' => '2026-07-16',
            'Recesso Escolar' => '2026-07-17',
            'Recesso Escolar' => '2026-07-18',
            'Formação Pedagógica / Organização' => '2026-07-20',
            'Formação Pedagógica / Organização' => '2026-07-21',
            'Formação Pedagógica / Organização' => '2026-07-22',
            'Formação Pedagógica / Organização' => '2026-07-23',
            'Formação Pedagógica / Organização' => '2026-07-24',
            'Formação Pedagógica / Organização' => '2026-07-25',

            // --- AGOSTO ---
            'N. Sra. da Babilônia - Feriado Municipal' => '2026-08-15',

            // --- SETEMBRO ---
            'Independência - Feriado Nacional' => '2026-09-07',

            // --- OUTUBRO ---
    
          'Feriado para Professores' => '2026-10-12',
            'Feriado para Professores' => '2026-10-15',
            'Atividade Extracurricular Piscina' => '2026-10-23',
            'Dia do Servidor Público' => '2026-10-28',

            // --- NOVEMBRO ---
            'Finados - Feriado Nacional' => '2026-11-02',
            'Ponto facultativo - Emenda' => '2026-11-03',
            'Aniversário da Cidade - Feriado Municipal' => '2026-11-04',
            'Proclamação da República - Feriado Nacional' => '2026-11-15',
            'Consciência Negra - Feriado' => '2026-11-20',
            'Início do Período de Rematrículas (Amarelo)' => '2026-11-23',
            // ... o período em amarelo segue até 11/12

            // --- DEZEMBRO ---
            'Mostra UATI' => '2026-12-02',
            'Integração UATI e CEC' => '2026-12-08',
            'Integração UATI e CEC' => '2026-12-09',
            'Término das aulas' => '2026-12-12',
            'Atividade Pedagógica de encerramento' => '2026-12-14',
            'Atividade Pedagógica de encerramento' => '2026-12-15',
            'Reunião do CEU' => '2026-12-16',
            'Atividade Pedagógica de encerramento' => '2026-12-17',
            'Atividade Pedagógica de encerramento' => '2026-12-18',
            'Atividade Pedagógica de encerramento' => '2026-12-19',
            'Véspera de Natal - Ponto Facultativo' => '2026-12-24',
            'Natal - Feriado' => '2026-12-25',
            'Véspera de Ano Novo - Ponto Facultativo' => '2026-12-31',

                                    

                                
                                

       
                               ]; 

        $feriados = array_merge($feriados_nacionais,$feriados_estaduais,$feriados_municipais,$pontos_facultativos);

        asort($feriados);


        //dd($feriados);

        
        
        foreach($feriados as $feriado=>$data){
            $dia = DiaNaoLetivo::where('data',$data)->first();
            if(is_null($dia)){
                $novo_dia = new DiaNaoLetivo;
                $novo_dia->data = $data;
                $novo_dia->descricao = $feriado;
                $novo_dia->save();
            }
        }

        dd($feriados);

    }

   
}

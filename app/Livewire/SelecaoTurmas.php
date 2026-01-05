<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Turma; 

class SelecaoTurmas extends Component
{
    public $pessoa;
    public $turmasAtuais;
    public $turmasSelecionadas = [];

    public function selecionarTurma($turmaId)
    {
        if (!in_array($turmaId, $this->turmasSelecionadas)) {
            $this->turmasSelecionadas[] = $turmaId;
        }
    }

    public function removerTurma($turmaId)
    {
        $this->turmasSelecionadas = array_diff($this->turmasSelecionadas, [$turmaId]);
    }

    public function render()
    {
        // 1. Pegar dados das turmas já escolhidas
        $escolhidasAtuais = Turma::whereIn('id',array_merge($this->turmasAtuais->pluck('id')->toArray(), $this->turmasSelecionadas))->get();

        $escolhidasModel = Turma::whereIn('id', $this->turmasSelecionadas)->get();


        foreach($escolhidasModel as &$turma){
            $turma->nomeCurso = $turma->getNomeCurso();
        }

        // 2. Buscar turmas disponíveis (vagas > 0 e que passam nos requisitos)
        $todasDisponiveis = Turma::whereIn('status_matriculas',['aberta','online'])->get();
        foreach($todasDisponiveis as &$turma){
            $turma->nomeCurso = $turma->getNomeCurso();
            $pacote = \App\Models\TurmaDados::where('turma',$turma->id)->where('dado','pacote')->first();
            if($pacote)
                $turma->pacote = $pacote->valor;
        }
        $todasDisponiveis = $todasDisponiveis->sortBy('nomeCurso');

        // 3. Filtrar conflitos
        $turmasFiltradas = $todasDisponiveis->filter(function ($turma) use ($escolhidasAtuais) {
            // Se já está selecionada, não mostra na lista de "disponíveis"
            if (in_array($turma->id, $this->turmasSelecionadas)) return false;

            // Lógica de conflito: verifica se alguma escolhida bate no mesmo dia E horário
            foreach ($escolhidasAtuais as $escolhida) {
                $diasEmComum = array_intersect($turma->dias_semana, $escolhida->dias_semana);
                if (!empty($diasEmComum)) {
                    // Verifica sobreposição de horário
                    if ($turma->hora_inicio < $escolhida->hora_termino && $turma->hora_termino > $escolhida->hora_inicio) {
                        return false; // Conflito detectado!
                    }
                }
            }
            return true;
        });

        return view('livewire.selecao-turmas', [
            'turmasFiltradas' => $turmasFiltradas,
            'escolhidasModel' => $escolhidasModel
        ]);
    }
}
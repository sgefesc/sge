<div>
    <div wire:loading.delay class="position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Processando...</span>
        </div>
        <span class="ml-2">Atualizando horários...</span>
    </div>

    <form action="./confirmacao" method="post">
        @csrf
        {{-- Turmas Escolhidas --}}
        <p class="text-secondary"><small>Minha Seleção ({{ count($turmasSelecionadas) }})</small></p>
        <div id="lista-turmas-escolhidas">
            <div wire:loading.class="opacity-50">
            @foreach($escolhidasModel as $item)
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div><small>
                        <strong>{{ $item->id }} | {{ $item->nomeCurso }}</strong> <br>
                        
                        Toda {{ implode(', ', $item->dias_semana) }} - {{ $item->hora_inicio }} às {{ $item->hora_termino }} | Prof. {{ $item->professor->nome_simples }}. {{ $item->local->nome }}</small>
                        <input type="hidden" name="turma[]" value="{{ $item->id }}">

                        </small>
                    </div>
                    <button type="button" wire:loading.attr="disabled" wire:click="removerTurma({{ $item->id }})" class="close">&times;</button>
                </div>
            @endforeach
            </div>
        </div>

        <hr>

        {{-- Turmas Disponíveis (Filtradas via Livewire) --}}
        <p class="text-secondary"><small>Turmas Disponíveis (Conflitos ocultados automaticamente)</small></p>
        <div class="list-group" wire:loading.class="opacity-50">
            @foreach($turmasFiltradas as $turma)

                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    
                        
                    
                    <div>
                        
                       <small><strong>{{ $turma->id }} | {{ $turma->nomeCurso }}</strong> - De {{ $turma->data_inicio }} a {{$turma->data_termino}}<br>
                        Toda {{ implode(', ', $turma->dias_semana) }} - {{ $turma->hora_inicio }} às {{ $turma->hora_termino }} | Prof. {{ $turma->professor->nome_simples }}. {{ $turma->local->nome }}</small>
                    </div>
                    <div class="text-right">
                        <span class="d-block mb-1">{{ $turma->vagas - $turma->matriculados }} vagas</span>
                        <button type="button" wire:loading.attr="disabled" wire:click="selecionarTurma({{ $turma->id }})" class="btn btn-sm btn-primary">
                            <span wire:loading.remove wire:target="selecionarTurma({{ $turma->id }})">Selecionar</span>
                            <span wire:loading wire:target="selecionarTurma({{ $turma->id }})">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            <button class="btn btn-success" type="submit" {{ count($turmasSelecionadas) == 0 ? 'disabled' : '' }}>
                Continuar para Confirmação
            </button>
        </div>
    </form>
</div>
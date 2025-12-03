@extends('perfil.layout')
@section('titulo')
    Minhas matrículas - Perfil FESC
@endsection

@section('style')
    <style>
      .rodape{
        margin-bottom:0px;
        padding-top:1rem;
        border-bottom: 1px solid WhiteSmoke;
        padding-bottom: 1rem;
        
      }
      .rodape:hover{
        background-color: whitesmoke;
      }
      hr{
        margin-bottom: 0;
      }
    </style>
@endsection
@section('body')

<div class="card mb-3">
                      
    <div class="card-body">
      <div class="row">
        <div class="col-sm-8">
          <h5 class="mb-0">Lista de Matriculas</h5>
          
          <p class="text-secondary"><small>Abaixo você encontrará a lista com suas matrículas.</small></p>
          
          
          
        </div>
        <div class="col-sm-4 text-right">
          <a class="btn btn-primary" href="/perfil/matricula/inscricao" title="Abre página para escolher os cursos que deseja se matricular.">Nova Matriculas</a>
        </div>
        
        
      </div>
      
      

      <hr>
      <div class="row">
        <div class="col-sm-12">
          @if($errors->any())
            @foreach($errors->all() as $erro)
                <div class="alert alert-warning">
                        <button type="button" class="close" data-dismiss="alert" >×</button>       
                        <p class="modal-title"><i class="fa fa-warning"></i> {{$erro}}</p>
                </div>
            @endforeach
          @endif
          @foreach($matriculas as $matricula)
                @foreach($matricula->inscricoes as $inscricao)
                <div class="form-group row rodape" title="Inscrição {{$inscricao->status}}">
                  
                  <div class="col-sm-9">
                    @switch($inscricao->status)
                      @case('regular')
                        <span class="badge border-primary bg-white">Regular</span>
                        @break
                      @case('cancelada')
                        <span class="badge badge-secondary">Cancelada</span>
                        @break
                      @case('pendente')
                        <span class="badge badge-warning" title="Procure a secretaria para saber mais detalhes">Pendente</span>
                        @break
                      @case('expirada')
                        <span class="badge badge-secondary">Encerrada</span>
                        @break
                      @case('espera')
                        <span class="badge badge-info" title="Aguardando o início das aulas">Aguardando início</span>
                        @break
                       @case('transferida')
                        <span class="badge badge-secondary" title="Aguardando o início das aulas">Transferida</span>
                        @break
                    
                      @default
                        
                    @endswitch
                    <strong>Turma {{$inscricao->turma->id.' - '.$inscricao->turma->getNomeCurso()}}</strong><br>  <small>De {{$inscricao->turma->data_inicio}} a {{$inscricao->turma->data_termino}}</small>
                    <small> toda {{implode(', ',$inscricao->turma->dias_semana)}} - {{$inscricao->turma->hora_inicio}} ás {{$inscricao->turma->hora_termino}} | Prof. {{$inscricao->turma->professor->nome_simples}}</small>
                    
      
                  </div>
                  <div class="col-sm-2 text-right">
                    @if($inscricao->status='pendente')
                    <a href="#"  class="btn btn-outline-secondary btn-sm" onclick="alert('Pendências encontradas: aguarde a validação do seu atestado ou entre em contato com a secretaria')" title="Termo de matrícula disponível após resolução das pendências">
                      Termo</a>
                    @else
                    <a href="/perfil/matricula/termo/{{$matricula->id}}?type=ead" target="_blank" class="btn btn-outline-info btn-sm" title="Termo de matrícula">
                      Termo</a>
                    @endif
                  </div>
                </div>



                @endforeach
          @endforeach
         

          
        
      

      
      
    </div>

  </div>


@endsection

@section('scripts')
<script>
  function cancelar(id,nome){
    if(confirm('Deseja mesmo cancelar a matrícula do curso "'+nome+ '" ?'))
      window.location.href = './cancelar/'+id;
  }



 </script>   
@endsection
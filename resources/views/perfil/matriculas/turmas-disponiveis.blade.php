@extends('perfil.layout')
@section('titulo')
    Perfil FESC - Me matricular
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

      .opacity-50 {
        opacity: 0.5;
        pointer-events: none; /* Impede cliques múltiplos enquanto carrega */
        transition: opacity 0.3s ease;
    }
    
    /* Loader flutuante discreto */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.2em;
    }

    .codigo-turma {
        font-family: monospace;
        font-weight: bold;
        width: 1rem;
    }
    </style>
@endsection
@section('body')
<form action="./confirmacao" method="post">
<div class="card mb-3">
                      
    <div class="card-body">
      <div class="row">
        <div class="col-sm-12">
          <h5 class="mb-0">Matricule-se agora mesmo</h6>
          
          <p class="text-secondary"><small>Escolha as turmas que deseja se matricular.</small></p>
          <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert" >×</button>       
            <p class="modal-title"><i class="fa fa-warning"></i> Menores de 18 anos necessitam de <a href="/perfil/atestado/cadastrar">autorização dos pais</a> para efetivação da matrícula. Em caso de dúvidas entre em contato pelo <a href="https://wa.me/551633620580">WhatsApp da FESC</a>
            <br> A lista de jogos utilizados e as faixas etárias no Centro de Treinamento Gammer estão disponíveis no <a href="/repositorio/jogos_ct_gammer.pdf">AQUI</a>.
            <br> Antes de se matricular em disciplinas online,verifique se sua conexão e seu equipamento de acesso suportam o aplicativo Microsoft Teams</p>
          </div>
         
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" >×</button>       
            <p class="modal-title"><i class="fa fa-danger"></i>Para visualizar as atividades físicas é necessário enviar o atestados de saúde e que ele seja aprovado. <a href="/perfil/atestado/cadastrar">Clique aqui</a> para enviar seu atestado.</p>
            
          </div>
          <hr>
          <br>
          @if($errors->any())
            @foreach($errors->all() as $erro)
                <div class="alert alert-warning">
                        <button type="button" class="close" data-dismiss="alert" >×</button>       
                        <p class="modal-title"><i class="fa fa-warning"></i> {{$erro}}</p>
                </div>
            @endforeach
          @endif
          <p class="text-secondary"><small>Turmas atuais.</small></p>
          <div id="lista-turmas-atuais">
            @foreach($turmasAtuais as $turma)
             <div class="alert alert-secondary">     
                <p class="modal-title"><i class="fa fa-secondary"></i>
                <strong>{{$turma->nome}}</strong>: {{implode(', ',$turma->dias_semana)}} das {{$turma->hora_inicio}} as {{$turma->hora_termino}}. Prof. {{$turma->professor->nome_simples}}. FESC {{$turma->local->nome}}
                </p>
                  
             </div>
            @endforeach

          </div>
          <hr>
          <br>

        @livewire('selecao-turmas', ['pessoa' => $pessoa, 'turmasAtuais' => $turmasAtuais])
      

      
      
    </div>

  </div>
</form>
@endsection

@section('scripts')
<script>
  

 </script>   
@endsection
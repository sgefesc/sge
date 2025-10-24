@extends('layout.app')
@section('titulo')Cadastro Tag de acesso @endsection
@section('pagina')
@include('inc.errors')
@if($pessoa !=null)
<div class="title-search-block">
    <div class="title-block">
        <h3 class="title">Alun{{$pessoa->getArtigoGenero($pessoa->genero)}}: {{$pessoa->nome}} 
            @if(isset($pessoa->nome_resgistro))
                ({{$pessoa->nome_resgistro}})
            @endif
           
        </h3>
        <p class="title-description"> <b> Cod. {{$pessoa->id}}</b> - Tel. {{$pessoa->telefone}} </p>
    </div>
</div>




<form name="item" method="post" action="/tags/{{$pessoa->id}}/criar" enctype="multipart/form-data">
    <input type="hidden" name="pessoa" value="{{$pessoa->id}}">
    <div class="card card-block">
        <div class="subtitle-block">
                <h3 class="subtitle"><i class=" fa fa-tag "></i> Cadastrar Tag. </h3>
            </div>
            <div class="form-group row"> 
                <label class="col-sm-1 form-control-label text-xs-right">
                    Tag
                </label>
                <div class="col-sm-3"> 
                    <input type="text" class="form-control boxed" name="tag" placeholder="" required autofocus> 
                </div>
                
                <div class="col-sm-3"> 
                    <button type="submit" name="btn"  class="btn btn-primary">Salvar</button>

                </div>
            </div>
        </div>
    
@endif

{{csrf_field()}}
    <div class="card card-block">
        @if($tags->count()>0)
        <div class="subtitle-block">
            <h3 class="subtitle"><i class=" fa fa-ticket "></i> Tags cadastrada </h3>
        </div>
        @foreach($tags as $tag)
        <div class="form-group row"> 
			<label class="col-sm-1 form-control-label text-xs-right">
				Tag <br>
                {{ $tag->id }}
			</label>
			<div class="col-sm-3"> 
				<strong>{{$tag->tag}}</strong><br>
                {{$tag->getNomePessoa()}}
                <br>


            </div>
            
			<div class="col-sm-3"> 
                <a href="#" class="btn btn-sm btn-danger-outline" onclick="excluir('{{$tag->id}}')" title="Excluir tag do cadastro"><i class="fa fa-times"></i> Excluir tag</a>
                @if(!$tag->livre_acesso)
                <a href="#" class="btn btn-sm btn-info-outline" onclick="addAdmin('{{$tag->id}}')" title="Liberar acesso"><i class="fa fa-plus"></i> Liberar acesso</a>
                @else
                <a href="#" class="btn btn-sm btn-warning-outline" onclick="remAdmin('{{$tag->id}}')"title="Remover o acesso livre" ><i class="fa fa-minus" ></i> Limitar Acesso</a>
                @endif

			</div>
		</div>
        @endforeach
      
    	
        
	

		
    </div>
</form>
@endif

@if($pessoa !=null)
<div class="card card-block">
    <div class="subtitle-block">
        <h3 class="subtitle"><i class=" fa fa-ticket "></i> Registros de tags vinculadas </h3>
    </div>
    <table class="table table-sm">
        <tr>
            <td>ID</td>
            <td>tag</td>
            <td>evento</td>
            <td>data</td>
          
        </tr>
        @foreach($logs as $log)
            <tr>
            <td>{{$log->id}}</td>
            <td><a href="/secretaria/atender/{{$log->tag}}">{{$log->tag}}</a></td>
            <td>{{$log->evento}}</td>
            <td>{{$log->data->format('d/m/Y H:i:s')}}</td>
            
            </tr>
        @endforeach
    </table>
 
</div>
@endif
@if($pessoa !=null)
{{$logs->links()}}
@else
{{$tags->links()}}
@endif
@endsection
@section('scripts')
<script>
    $(".alert").delay(5000).slideUp(1000, function () {
        $(this).alert('close');
    });
function excluir(id){
    if(confirm('Deseja mesmo apagar este item?')){
        console.log('pagar item');
        resource = $.ajax('./apagar/'+id)
                        .done( function(){
                            location.reload();
                        })
                        .fail(function(){
                            alert('Erro ao processar a exclusão: '+resource.statusText);
                       });
    }
}
function addAdmin(id){
    if(confirm('Adicionar livre acesso?')){
        console.log('pagar item');
        resource = $.ajax('./adiciona-livre-acesso/'+id)
                        .done( function(){
                            location.reload();
                        })
                        .fail(function(){
                            alert('Erro ao processar a exclusão: '+resource.statusText);
                       });
    }
}
function remAdmin(id){
    if(confirm('Remover o livre acesso?')){
        console.log('pagar item');
        resource = $.ajax('./adiciona-livre-acesso/'+id)
                        .done( function(){
                            location.reload();
                        })
                        .fail(function(){
                            alert('Erro ao processar a exclusão: '+resource.statusText);
                       });
    }
}
</script>
@endsection
@extends('layout.app')
@section('pagina')
<div class="title-search-block">
    <div class="title-block">
        <div class="row">
            <div class="col-md-6">
                <h3 class="title"> Disciplinas <a href="{{asset('/cursos/disciplinas/cadastrar')}}" class="btn btn-primary btn-sm rounded-s">Adicionar</a>               
	                



                </h3>
                <p class="title-description"> Lista de disciplinas dos cursos ofertados </p>
            </div>
        </div>
    </div>

    @include('inc.errors')
    <div class="items-search">
        <form class="form-inline" method="get">
        {{csrf_field()}}
            <div class="input-group"> 
            	<input type="text" class="form-control boxed rounded-s" name="buscar" placeholder="Procurar...">
            	<span class="input-group-btn">
					<button class="btn btn-secondary rounded-s" type="submit">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
        </form>
    </div>
</div>

@if(isset($disciplinas) && count($disciplinas))
<div class="card items">
    <ul class="item-list striped"> <!-- lista com itens encontrados -->
        <li class="item item-list-header hidden-sm-down">
            <div class="item-row">
                <div class="item-col fixed item-col-check"> 
                	<label class="item-check" id="select-all-items">
                		<input type="checkbox" class="checkbox" value="0"><span></span>
					</label>
				</div>
                <div class="item-col item-col-header item-col-title">
                    <div> <span>Disciplina</span> </div>
                </div>
                <div class="item-col item-col-header item-col-sales">
                    <div> <span>Programa</span> </div>
                </div>
                <div class="item-col item-col-header item-col-sales">
                    <div> <span>Vagas</span> </div>
                </div>
                <div class="item-col item-col-header item-col-sales">
                    <div> <span>Carga Horária</span> </div>
                </div>
                <div class="item-col item-col-header fixed item-col-actions-dropdown"> </div>
            </div>
        </li>
        @foreach($disciplinas as $disciplina)
        <li class="item">
            <div class="item-row">
                <div class="item-col fixed item-col-check"> 
                	<label class="item-check" id="select-all-items">
						<input type="checkbox" class="checkbox" {{ $disciplina->checked }}  name="disciplina[{{ $disciplina->id }}]" value="{{ $disciplina->id }}">
						<span></span>
					</label> </div>                
                <div class="item-col fixed pull-left item-col-title">
                    <div class="item-heading">Disciplina</div>
                    <div>                        
                    <h4 class="item-title"><a href="/cursos/disciplinas/disciplina/{{$disciplina->id}}">{{ $disciplina->nome }}</a> </h4>
                    </div>
                </div>
                <div class="item-col item-col-sales">
                    <div class="item-heading">Programa</div>
                    <div> {{$disciplina->programa->sigla}}</div>
                </div>
                <div class="item-col item-col-sales">
                    <div class="item-heading">Vagas</div>
                    <div>
                        {{$disciplina->vagas}}

                    </div>
                </div> 
                <div class="item-col item-col-sales">
                    <div class="item-heading">Carga horária</div>
                    <div>{{$disciplina->carga}} hs</div>
                </div> 

                <div class="item-col fixed item-col-actions-dropdown">
                    <div class="item-actions-dropdown">
                        <a class="item-actions-toggle-btn"> <span class="inactive">
				<i class="fa fa-cog"></i>
			</span> <span class="active">
			<i class="fa fa-chevron-circle-right"></i>
			</span> </a>
                        <div class="item-actions-block">
                            <ul class="item-actions-list">
                                <li>
                                    <a class="remove" onclick="apagar({{$disciplina->id}})" href="#" title="Apagar"> <i class="fa fa-trash "></i> </a>
                                </li>
                                <li>
                                <a class="edit" onclick="editar({{$disciplina->id}})" href="#" title="Editar"> <i class="fa fa-pencil "></i> </a>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        @endforeach


    </ul>
</div>
@if(method_exists($disciplinas, 'links'))
<nav class="text-xs-right">
{!! $disciplinas->links()  !!}
</nav>
@endif


@else
<h3 class="title-description"> Nenhuma disciplina encontrada </p>
@endif

@endsection
@section('scripts')
<script>
    function apagar(item)
    {
        if(confirm("Tem certeza que deseja apagar essa disciplina?"))
        {
            $(location).attr('href','{{asset("/cursos/disciplinas/apagar")}}/?disciplina='+item);
        }
    }
    function editar (item)
    {
        $(location).attr('href','{{asset("/cursos/disciplinas/editar")}}/'+item);
    }
</script>
@endsection

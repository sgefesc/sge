@extends('layout.app')
@section('titulo')Gerenciador de boletos @endsection
@section('pagina')
<style>
@media (min-width: 767px){
    .codigo{
        max-width: 50px; }
    .pessoa{
        max-width: 300px; }
    .pedidoem{
        max-width: 80px; }
        
        
    }
@media (max-width: 766px){
    .pessoa{
        font-size: 20px;
    }
}
    
</style>
<ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="/">Início</a></li>
  <li class="breadcrumb-item"><a href="#">Boletos</a></li>
 
</ol>

<div class="title-search-block">
    <div class="title-block">
        <div class="row">
            <div class="col-md-6">

                <h3 class="title"> Ferramenta de gerenciamento de boletos <u>{{$tipo}}</u></h3>

                <p class="title-description"> Gerenciamento em lote de {{$boletos->total()}} boletos.</p>
            </div>
        </div>
    </div>


    <!--<div class="items-search">
        <form class="form-inline" method="GET>
        {{csrf_field()}}
            <div class="input-group"> 
                <input type="text" class="form-control boxed rounded-s" name="codigo" placeholder="Procurar p/ código.">
                <span class="input-group-btn">
                    <button class="btn btn-secondary rounded-s" type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
        </form>
    </div>-->
    <div class="items-search col-md-3">
        <div class="header-block header-block-search hidden-sm-down">
           <form action="?" method="GET">
            {{csrf_field()}}
               <div class="input-group input-group-sm" style="float:right;">
                   <input type="text" class="form-control" name="codigo" placeholder="Buscar por codigo">
                   <i class="input-group-addon fa fa-search" onclick="document.forms[1].submit();" style="cursor:pointer;"></i>
               </div>
           </form>
       </div>

   </div>
</div>
@include('inc.errors')
<form name="item" class="form-inline">
    <section class="section">
    <div class="row ">
        <div class="col-xl-12">
            <div class="card sameheight-item">
                <div class="card-block">
                    <!-- Nav tabs -->
                    <div class="row">
                        <div class="col-xs-6 text-xs">
                            {{ $boletos->links() }}
                        </div>
                        <div class="col-xs-6 text-xs-right">

                           
                            <div class="action dropdown pull-right "> 
                                <!-- <a href="#" class="btn btn-sm rounded-s btn-secondary" title="Exportar para excel"><img src="/img/excel.svg" alt="excel" width="20px"></a> -->
                                <button class="btn btn-sm rounded-s btn-secondary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Com os selecionados...
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenu1"> 
                
                                    <a class="dropdown-item" href="#" onclick="alterarStatus('aprovar')">
                                        <label><i class="fa fa-print icon text-secondary"></i> Imprimir</label>
                                    </a> 
                                    <a class="dropdown-item" href="#" onclick="alterarStatus('aprovar')">
                                        <label><i class="fa fa-times icon text-warning"></i> Cancelar</label>
                                    </a> 
                                    <!--
                                    <a class="dropdown-item text-danger" href="#" onclick="alterarStatus('aprovar')">
                                        <label><i class="fa fa-times icon text-danger"></i> CANCELAR MATRÍCULAS</label>
                                    </a> -->
                                    
                                    
                                    <a class="dropdown-item"  >
                                        <label> <small>Alterar boletos para...</small> </label>
                                    </a> 
                                    
                                    <a class="dropdown-item" href="#" onclick="alterarStatus('gravado')">
                                        <label><i class="fa fa-arrow-right icon text-secondary"></i> Gravado</label>
                                    </a> 
                                    <a class="dropdown-item" href="#" onclick="alterarStatus('impresso')">
                                        <label><i class="fa fa-arrow-right icon text-seconday"></i><span> Impresso</span></label>
                                    </a> 
                                    <a class="dropdown-item" href="#" onclick="alterarStatus('emitido')">
                                        <label><i class="fa fa-arrow-right icon text-secondary"></i><span> Emitido</span></label>
                                    </a> 
                                    <a class="dropdown-item" href="#" onclick="alterarStatus('cancelado')">
                                        <label><i class="fa fa-minus-circle icon text-danger"></i> <span> Cancelado</span></label>
                                    </a> 
                                     
                                    
                                </div>
                             </div>
                            
                        </div>

                    </div>
                    
                    <div class="tab-content tabs-bordered">

                        <!-- Tab panes ******************************************************************************** -->
                        
                                <section class="example">
                                    <div class="table-flip-scroll">

                                        <ul class="item-list striped">
                                            <li class="item item-list-header hidden-sm-down">
                                                <div class="item-row">
                                                    <div class="item-col fixed item-col-check">
                                                        <label class="item-check">
                                                        <input type="checkbox" class="checkbox" onchange="selectAllItens(this);">
                                                        <span></span>
                                                        </label> 
                                                    </div>
                                                    <div class="item-col item-col-header codigo">
                                                        <div> <span>Boleto<span> </div>
                                                    </div>
                    
                                                    <div class="item-col item-col-title item-col-header pessoa">
                                                        <div> <span>Pessoa</span> </div>
                                                    </div>
                                                    <div class="item-col item-col-header pedidoem">
                                                        <div> <span>Vencimento</span> </div>
                                                    </div>

                                                    <div class="item-col item-col-header ">
                                                        <div class="action dropdown pull-right "> 
                                                            <div id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor:pointer;">
                                                                 <span >Ref</span> 
                                                            </div>
                                                            <!--<div class="dropdown-menu" aria-labelledby="dropdownMenu2" id="filtro2" style="padding-left:10px;"> 
                                                                
                                                                    <label>
                                                                        <input class="checkbox" type="checkbox" onclick="parado();">
                                                                        <span>Option one</span>
                                                                    </label>
                                                                    <br>
                                                                    <label>
                                                                        <input class="checkbox" type="checkbox" onclick="parado();">
                                                                        <span>Option two</span>
                                                                    </label>
                                                                    <br>
                                                                    <button class="btn btn-sm btn-primary rounded-s">Aplicar</button>
                                                                
                                                            </div>-->
                                                        </div>
                                                    </div>
                                                    <div class="item-col item-col-header ">
                                                        <div> <span>Status</span> </div>
                                                    </div>

                                                    <div class="item-col item-col-header fixed item-col-actions-dropdown">&nbsp; </div>
                                                </div>
                                            </li>
                                            @foreach($boletos as $boleto)


                                                                                  
                                            <li class="item">
                                                <div class="item-row">


                                                    <div class="item-col fixed item-col-check" > 
                                                        <label class="item-check" >
                                                        <input type="checkbox" class="checkbox" name="boleto" value="{{$boleto->id}}">
                                                        <span></span>
                                                        </label>
                                                    </div>


                                                    <div class="item-col item-col codigo">
                                                        <div class="item-heading">id</div>
                                                        <div > <small><a href="/financeiro/boletos/informacoes/{{$boleto->id}} ">{{$boleto->id}} </a></small></div>
                                                    </div>
                                        
                                                    
                                                    <div class="item-col item-col-title pessoa">
                                                        <div class="item-heading">Pessoa</div>
                                                        <div> <a href="/secretaria/atender/{{$boleto->pessoa}}"> {{$boleto->getNomePessoa()}}</a></div> 
                                                    </div>


                                                    <div class="item-col item-col pedidoem" >
                                                        <div class="item-heading">Data</div>
                                                        <div> 
                                                            <small>{{$boleto->vencimento->format('d/m/Y')}}</small>
                                                        </div>
                                                    </div>



                                                    <div class="item-col item-col">
                                                        <div class="item-heading">Tipo</div>
                                                     
                                                        <div><small>
                                                            @foreach($boleto->lancamentos as $lancamento)
                                                            {{$lancamento->referencia}}<br/>
                                                            @endforeach
                                                        </small></div>
                                                     
                                                    </div>
                                                     
                                                   
                                                    <div class="item-col item-col">
                                                        <div class="item-heading">Status</div>
                                                        <div> @if($boleto->status == 'gravado')
                                                            <span class="badge badge-pill badge-secondary">
                                                            @elseif($boleto->status == 'impresso')
                                                            <span class="badge badge-pill badge-primary">
                                                            @elseif($boleto->status == 'emitido')
                                                            <span class="badge badge-pill badge-info">
                                                            @elseif($boleto->status == 'cancelado')
                                                            <span class="badge badge-pill badge-danger">
                                                            @elseif($boleto->status == 'pago')
                                                            <span class="badge badge-pill badge-success">
                                                            @else
                                                            <span class="badge badge-pill badge-secondary">
                                                            @endif
                                                            {{$boleto->status}}</span>
                                                        </div>
                                                    </div>


                                                    <div class="item-col fixed item-col-actions-dropdown">
                                                        <div class="item-actions-dropdown">
                                                            <a class="item-actions-toggle-btn"> 
                                                                <span class="inactive">
                                                                    <i class="fa fa-cog"></i>
                                                                </span> 
                                                                <span class="active">
                                                                    <i class="fa fa-chevron-circle-right"></i>
                                                                </span>
                                                            </a>
                                                            <div class="item-actions-block">
                                                                <ul class="item-actions-list">
                                                                    <!--
                                                                    <li>
                                                                     <a class="edit" title="Aprovar" href="#" onclick="alterarStatusIndividual('aprovar','{{$boleto->id}}')"> <i class="fa fa-check-circle-o "></i> </a>
                                                                    </li>
                                                                    <li>
                                                                     <a class="remove" title="Negar" href="#" onclick="alterarStatusIndividual('negar','{{$boleto->id}}')"> <i class="fa fa-ban "></i> </a>
                                                                    </li>
                                                                    <li>
                                                                     <a class="edit" title="Colocar para análise" href="#" onclick="alterarStatusIndividual('analisando','{{$boleto->id}}')"> <i class="fa fa-clock-o "></i> </a>
                                                                    </li>
                                                                    <li>
                                                                     <a class="remove" title="Cancelar" href="#" onclick="alterarStatusIndividual('cancelar','{{$boleto->id}}')"> <i class="fa fa-minus-circle "></i> </a>
                                                                    </li>
                                                                -->
                                                                
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
                                </section>
                            
                    </div>
                </div>
                <!-- /.card-block -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col-xl-6 -->
        
        <!-- /.col-xl-6 -->
    </div>

{{ $boletos->links() }}

</form>
</section>
@endsection
@section('scripts')
<script>
function alterarStatusIndividual(status,id){
     
        if(id=='')
            alert('Nenhum item selecionado');
        else
        alert('função ainda não disponível');
    
}

function alterarStatus(status){
     var selecionados='';
        $("input:checkbox[name=boleto]:checked").each(function () {
            selecionados+=this.value+',';

        });
        if(selecionados==''){
            alert('Nenhum item selecionado');
            return false;
        }
        alert('Função ainda não disponível');
        /*
        if(status ==  'aprovar' || status ==  'negar' )
            //$(location).attr('href','./analisar/'+selecionados);
        else
            if(confirm('Deseja realmente alterar as bolsas selecionadas?'))
                //$(location).attr('href','./status/'+status+'/'+selecionados);

        return false;
        */

    
}

function parado(){
    console.log('parei');
    //$('#filtro2').css('display','inline');
    $('#dropdownMenu2').trigger('click');
}


</script>



@endsection
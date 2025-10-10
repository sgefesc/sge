<div class="tab-pane fade" id="documentos">
    <div class="row"> 
            <div class="col-xs-6">
                <a href="/pessoa/atestado/cadastrar/{{$pessoa->id}}" class="btn btn-primary btn-sm rounded-s"> Adicionar</a>
                
                                                       
            </div>                                           
           
        </div> 

    <section class="card card-block">
            
            <div class="form-group row"> 
                    <label class="col-sm-4 form-control-label text-xs-right">Documentos</label>
                    <div class="col-sm-8">
                        
                        @if(isset($documentos))
                        @foreach($documentos as $documento)
                        <div class="row">
                            
                            <div class="col-sm-1" style="width: 3rem"><strong><a href="/pessoa/atestado/mostrar/{{$documento->id}}" title="Visualizar do documento">{{$documento->id}}</strong></a></div>
                            <div class="col-sm-2">
                                <span  
                                @switch($documento->status)
                                @case('aprovado')
                                    class="badge badge-pill badge-success"
                                @break;
                                @case('analisando')
                                    class="badge badge-pill badge-warning"
                                @break;
                                @case('negado')
                                    class="badge badge-pill badge-danger"
                                @break;
                                @case('vencido')
                                    class="badge badge-pill badge-secondary"
                                @break;
                                @default
                                    class="badge badge-pill badge-secondary"
                                @break;
                                @endswitch

                            
                                 style="font-size: 0.8rem">{{$documento->status}}</span></div>
                            <div class="col-sm-3">Tipo: {{$documento->tipo}}</div>
                            <div class="col-sm-2">Emissão: {{\Carbon\Carbon::parse($documento->emissao)->format('d/m/Y')}}</div>
                            <div class="col-sm-3">
                               
                                <a href="/pessoa/atestado/analisar/{{$documento->id}}" title="Análise do documento"><i class="fa fa-pencil-square-o text-success"></i></a>
                                <a href="/pessoa/atestado/editar/{{$documento->id}}" title="Editar documento"><i class="fa fa-pencil text-secondary"></i></a>
                                <a href="#" onclick="desativardocumento('{{$documento->id}}')" title="Apagar documento"><i class="fa fa-times text-danger"></i></a>
                            </div>  
                        
                        </div>
                        
                            <br>
                        @endforeach
                        @endif


                    </div>

            </div>
            
   </section>

</div>
<script>
    function desativardocumento(id){
    if(confirm("Deseja mesmo arquivar esse documento?")){
        $(location).attr('href', '{{asset('/pessoa/documento/arquivar')}}/'+id);
    }

}
</script>
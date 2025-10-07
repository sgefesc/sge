@extends('layout.app')
@section('pagina')
<div class="title-block">
	
    <h3 class="title"> Envio de boletos via API de Cobrança do BB <span class="sparkline bar" data-type="bar"></span> </h3>
    
</div>
@include('inc.errors')
	
@foreach($registros as $reg)
    <div class="card card-block">
	   <div class="row">
			<div class="col-md-1">
				@if($reg->status=='ok')
					<span style="color:green;font-size:20pt;"><i class="fa fa-check"></i></span>
				@else
					<span style="color:red;font-size:20pt;"><i class="fa fa-exclamation"></i></span>
				@endif
				
			</div>
			<div class="col-md-2">
				<strong><a href="/financeiro/boletos/informacoes/{{$reg->boleto}}">{{$reg->boleto}}</a></strong>
			</div>
			<div class="col-md-9">
				<strong>{{$reg->msg}}</strong>
			</div>
	   </div>
   </div>
@endforeach			            
		<div class="form-group row">
			<div class="col-sm-10 col-sm-offset-2">
				<a href="/secretaria/atendimento" class="btn btn-primary">Voltar</a>
				<!-- 
				<button type="submit" class="btn btn-primary"> Cadastrar</button> 
				-->
			</div>
       </div>
    </div>
    {{csrf_field()}}
</form>
        
@endsection

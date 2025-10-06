@extends('layout.app')
@section('pagina')
<div class="title-block">
	
    <h3 class="title"> Status PIX do boleto {{$boleto}} via API de Cobrança do BB <span class="sparkline bar" data-type="bar"></span> </h3>
    
</div>
@include('inc.errors')
<form name="item" action="https://mpag.bb.com.br/site/mpag/" method="POST">

	<input type="hidden" name="boletos" value="318737">
	

    <div class="card card-block">
	   <div class="row">
			<div class="col-3">
				<strong>ID do boleto:</strong>
			</div>
			<div class="col-9">
				{{$pix->boleto_id?$pix->boleto_id:'N/A'}}
			</div>
	   </div>
	   <div class="row">
			<div class="col-3">
				<strong>txID</strong>
			</div>
			<div class="col-9">
				{{$pix->txid?$pix->txid:'N/A'}}
			</div>		
	   </div>
	   <div class="row">
			<div class="col-3">
				<strong>URL</strong>
			</div>
			<div class="col-9">
				{{$pix->url?$pix->url:'N/A'}} 
			</div>			
	   </div>
	   <div class="row">
			<div class="col-12">
				@if($pix->url)
				<img src="{{asset('/img/qrcode.php?code='.$pix->eml) }}" alt="QR Code Pix">
				@else
				<p>Não há QR Code: erro.</p>
				@endif
			</div>		
	   </div>
		
   </div>			            
		<div class="form-group row">
			<div class="col-sm-10 col-sm-offset-2">
                <button type="cancel" name="btn" class="btn btn-primary" onclick="history.back(-2);return false;">Voltar</button>
				<!-- 
				<button type="submit" class="btn btn-primary"> Cadastrar</button> 
				-->
			</div>
       </div>
    </div>
    {{csrf_field()}}
</form>
        
@endsection

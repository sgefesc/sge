@extends('layout.app')
@section('titulo')Gerador de arquivo de remessa @endsection
@section('pagina')
</body>
<header>
    

    <script type="text/javascript">
    	@if($boletos->hasMorePages())
    	var next = true;
    	@else
    	var next = false;

    	@endif
    	function loadNext(){
    		if(next){
    			setTimeout(mudar('{{$boletos->nextPageUrl()}}'), 2000);
    		}
    		else{
    			
                $('.btn').fadeIn('slow');
    		}

    	}
    	function mudar(url='#'){
    		/*
    		if(url=='')
    			url = '#';*/
    		//alert('chamado no goTo>'+url);
    		window.location.replace(url);
    	}
        

    </script>
    
  </header>
  <body onload="loadNext();">

    <!-- conteúdo da página -->
    
@include('inc.errors')

<div class="title-block">
    <div class="row">
        <div class="col-md-7">
            <h3 class="title">Geração de Arquivo de Remessa </h3>
            <p class="title-description">Processando página {{$boletos->currentPage()}} de {{$boletos->lastPage()}}. </p>
        </div>
    </div>
</div>
<p>{{ceil($boletos->currentPage()*100/$boletos->lastPage())}}% processado...</p>

<div id="progressbar"></div>
<br>
@for($i=1;$i<=$boletos->lastPage();$i++)
<a href="{{asset('/financeiro/boletos/remessa/download')}}/{{date('Ymd').'_'.$i.'.rem'}}" target="_blank" class="btn btn-warning" style="display:none;">Baixar arquivo {{$i}}</a><br>
@endfor
<a  href="/financeiro/boletos/home" class="btn btn-primary" style="display:none;">Menu Boletos</a>



    </div>
    <img src="{{asset('/img/loading.gif')}}" with="25px" height="25px">
    <div id="progressbar"><div class="progress-label">Processando...</div></div>
  
  </body>
</html>
@endsection
@section('scripts')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#progressbar" ).progressbar({
      value: {{ceil($boletos->currentPage()*100/$boletos->lastPage())}}
    });
  } );
  </script>
@endsection

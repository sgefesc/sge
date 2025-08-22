@extends('layout.app')
@section('titulo')
Gerador de carnês 
@endsection
@section('pagina')
</body>
<header>
    

    <script type="text/javascript">

    	@if($matriculas->hasMorePages())
    	var next = true;
    	@else
    	var next = false;

    	@endif
    	function loadNext(){
    		if(next){
    			setTimeout(mudar('{{$matriculas->nextPageUrl()}}'), 20000);
    		}
    		else{
    			
                setTimeout(mudar('./fase3'), 20000);
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
            <h3 class="title">Geração de Carnês</h3>
            <p class="title-description">Processando página  </p>
        </div>
    </div>
</div>
<p>processado...</p>

<div id="progressbar"></div>
<br>
<a id="botao" href="/financeiro/boletos/home" class="btn btn-primary" style="display:none;">Menu Boletos</a>




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
      value: 53
    });
  } );
  </script>
@endsection

@extends('layout.app')
@section('titulo')
Gerador de carnês 
@endsection
@section('pagina')
<header>
    
      <style>
  .ui-progressbar {
    position: relative;
  }
  .progress-label {
    position: absolute;
    left: 45%;
    top: 4px;
    font-weight: bold;
    text-shadow: 1px 1px 0 #fff;
  }
  </style>
     <script type="text/javascript">

      @if($boletos->hasMorePages())
      var next = true;
      @else
      var next = false;

      @endif
      function loadNext(){
       if(next){
          window.setTimeout(function(){
            window.location.reload();
          }, 2000);
        }
        else{
          
                setTimeout(mudar('./gerar-pdfs'), 20000);
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
            <p class="title-description">Geração automática de lançamentos e boletos de matrículas ativas e pendentes.</p>
        </div>
    </div>
</div>
<div class="card card-block">
      <div class="row">
        <div class="col-md-1" style="width: 40px"><img src="{{asset('/img/loading.gif')}}" with="25px" height="25px"></div>
        <div class="col-md-11">
            <h3 class="title">Fase 1 - Registrando os boletos</h3>
            <p class="title-description">Obtendo os QR Codes para pagamento via pix</p>
            <br>
        </div>
      </div>
      
      <div class="form-group row"> 
        <label class="col-sm-2 form-control-label text-xs-right">
          Registrando Boletos
        </label>
        <div class="col-sm-5"> 
          <div class="input-group">
           <div id="progressbar3"><div class="progress-label">{{ceil($boletos->currentPage()*100/$boletos->lastPage())}}%</div></div>
          </div>
        </div>
      </div>
      <div class="form-group row"> 
        <label class="col-sm-2 form-control-label text-xs-right">
          Geração dos arquivos PDF
        </label>
        <div class="col-sm-5"> 
          <div class="input-group">
           <div id="progressbar4"><div class="progress-label">Aguardando...</div></div>
          </div>
        </div>
      </div>
      
</div>
    
  
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
      value: 100
    });
    $( "#progressbar2" ).progressbar({
      value: 100
    });
     $( "#progressbar3" ).progressbar({
       value: {{ceil($boletos->currentPage()*100/$boletos->lastPage())}}
    });
      $( "#progressbar4" ).progressbar({
      value: 0
    });
       $( "#progressbar5" ).progressbar({
      value: 0
    });
        $( "#progressbar6" ).progressbar({
      value: 0
    });
        $( "#progressbar7" ).progressbar({
     value: 100
    });
  } );
  </script>
@endsection

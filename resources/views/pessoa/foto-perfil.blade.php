@extends('layout.app')
@section('titulo')Alteração de foto de perfil @endsection
@section('pagina')

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
@include('inc.errors')


<form action="?" method="POST" enctype="multipart/form-data" class="mt-4">
    @csrf
     <div class="row">
        <div class="col-md-6 center-block">
            <div class="card card-primary">
                <div class="card-header">
                    <div class="header-block">
                        <p class="title" style="color:white">Câmera</p>
                    </div>
                </div>
                <div class="card-block text-center">
                    <video id="video" autoplay style="width:100%; border-radius:10px; border:2px solid #ddd; object-fit:cover;"> </video>
                   

                </div>
                
            </div>
        </div>
        <div class="col-md-6 center-block">
            <div class="card card-primary">
                <div class="card-header">
                    <div class="header-block">
                        <p class="title" style="color:white">Captura</p>
                    </div>
                </div>
                <div class="card-block justify-content-center align-items-center">
                    <canvas id="canvas" 
                style="display:none; width:100%; border:2px solid #ddd; border-radius:10px;" class="text-center">
            </canvas>
                   

                </div>
                
            </div>
        </div>
       
    </div>
    <div class="row">
        <div class="col-md-6 center-block">
            <div class="text-center mt-3">
                <button type="button" id="btnFoto" class="btn btn-primary">
                    Capturar Foto
                </button>
                <button type="submit" class="btn btn-success" id="btnSalvarFoto" style="display:none;">
            Salvar foto capturada
                </button>
            </div>
            <div class="text-center mt-3">
                
            </div>
        </div>
    </div>
    <input type="hidden" name="foto" id="fotoBase64">
    <input type="hidden" name="pessoa" value="{{$pessoa->id}}">



    
</form>
@endsection
@section('scripts')
<script>
    const video = document.getElementById("video");
    const canvas = document.getElementById("canvas");
    const ctx = canvas.getContext("2d");
    const btnFoto = document.getElementById("btnFoto");
    const btnSalvarFoto = document.getElementById("btnSalvarFoto");

    navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: { exact: "environment" } // força a câmera traseira
        }
    })
    .then(stream => {
        video.srcObject = stream;
    })
    .catch(err => {
        console.log("Erro ao acessar câmera traseira:", err);

        // fallback automático para câmera frontal
        navigator.mediaDevices.getUserMedia({
            video: true
        }).then(stream => {
            video.srcObject = stream;
        });
    });

    // Capturar foto SEM DISTORCER e em 500x500
    btnFoto.addEventListener("click", () => {

        // Define tamanho final
        const FINAL_SIZE = 550;

        canvas.width = FINAL_SIZE;
        canvas.height = FINAL_SIZE;

        const vw = video.videoWidth;   // largura real do vídeo
        const vh = video.videoHeight;  // altura real do vídeo

        // Calcula proporção para crop central (preenchendo o quadrado)
        const size = Math.min(vw, vh); 
        const sx = (vw - size) / 2;     // início do corte no eixo X
        const sy = (vh - size) / 2;     // início do corte no eixo Y

        // Desenha no canvas com crop e redimensiona para 500x500
        ctx.drawImage(video, sx, sy, size, size, 0, 0, FINAL_SIZE, FINAL_SIZE);

        // Mostra o canvas e botão salvar
        canvas.style.display = "block";
        btnSalvarFoto.style.display = "inline-block";

        const base64 = canvas.toDataURL("image/png");
        fotoBase64.value = base64;
    });

</script>
@endsection
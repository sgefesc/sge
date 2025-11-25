<div class="modal fade in" id="modal-foto" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"> 
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" title="Fechar caixa">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-camera"></i> Identificação visual</h4>
            </div>
            <div class="modal-body">  
                <div class="row">
                    <div class="col-md-12">        
                        <img src="/arquivo/foto/{{$pessoa->id}}" alt="Foto de perfil" width="100%" class="mb-1">                   
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-center"> 
                        <a href="/pessoa/foto-perfil/{{$pessoa->id}}" title="Alterar ou remover foto" class="btn btn-primary" target="_self">
                            Trocar foto
                        </a>
                        <a href="#" title="Alterar ou remover foto" class="btn btn-warning" onclick="removerFotoPerfil();">
                            Remover foto
                        </a>     
                             
                    </div>
                </div>           
            </div>
            
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
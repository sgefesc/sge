@extends('layout.app')
@section('pagina')
<div class="title-block">
    <h3 class="title"> Cadastrar nova turma <span class="sparkline bar" data-type="bar"></span> </h3>
</div>
@include('inc.errors')
<form name="item" method="POST">
    <div class="card card-block">
		<div class="subtitle-block">
			<h3 class="title">Dados básicos obrigatórios</h3>
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Programa 
			</label>
			<div class="col-sm-6 "> 
				<select class="c-select form-control boxed" name="programa" required>
					<option >Selecione um programa</option>
					@if(isset($dados['programas']))
					@foreach($dados['programas'] as $programa)
					<option value="{{$programa->id}}">{{$programa->sigla.' - '.$programa->nome}}</option>
					@endforeach
					@endif
				</select> 
			</div>
			<div class="col-sm-4">&nbsp;</div>
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Curso/Atividade
			</label>
			<div class="col-sm-6"> 
				<div class="input-group">
					<span class="input-group-addon"><i class=" fa fa-toggle-right  "></i></span> 
					<input type="text" class="form-control boxed" id="fcurso" name="fcurso" placeholder="Digite e selecione. Cód. 307 para UATI" required> 
					<input type="hidden" name="curso">
				</div>
				<div class="col-sm-12"> 
				 <ul class="item-list" id="listacursos">
				 </ul>
				</div> 
			</div>
			
		</div>
		<div class="form-group row" id="row_modulos" style="display:none"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Modulo
			</label>
			<div class="col-sm-4"> 
				<input type="number" id="fmodulo" class="form-control boxed" name="modulo" min="1" placeholder="" > 
			</div>
			
		</div>
		<div class="form-group row" id="row_disciplina" style="display:none"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Disciplina
			</label>
			<div class="col-sm-6"> 
				<div class="input-group">
					<span class="input-group-addon"><i class=" fa fa-toggle-down "></i></span> 
					<input type="text" class="form-control boxed" id="fdisciplina" name="fdisciplina" placeholder="Digite e selecione" > 
					<input type="hidden" name="disciplina">
				</div>
				<div class="col-sm-12"> 
				 <ul class="item-list" id="listadisciplinas">
				 </ul>
				</div> 
			</div>
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Professor
			</label>
			<div class="col-sm-6"> 
				<select class="c-select form-control boxed" name="professor" required>
					<option>Selecione um professor</option>
					@if(isset($dados['professores']))
					@foreach($dados['professores'] as $professor)
					<option value="{{$professor->id}}">{{$professor->nome}}</option>
					@endforeach
					@endif
				</select> 
			</div>
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Unidade
			</label>
			<div class="col-sm-2"> 
				<select class="c-select form-control boxed" name="unidade" onchange="carregarSalas(this.value)" required >
					<option>Selecione ums unidade de atendimento</option>
					@if(isset($dados['unidades']))
					@foreach($dados['unidades'] as $unidade)
					<option value="{{$unidade->id}}">{{$unidade->nome}}</option>
					@endforeach
					@endif
				</select> 
			</div>
			<label class="col-sm-2 form-control-label text-xs-right">
				Sala
			</label>
			<div class="col-sm-2"> 
				<select class="c-select form-control boxed" name="sala" id="select_sala" required >
					<option>Selecione um local antes.</option>
				
				</select> 
			</div>
		</div>
		
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Parceria 
			</label>
			<div class="col-sm-6"> 
				<select class="c-select form-control boxed" name="parceria" required>
					<option value="0" >Selecione parceria, se houver</option>
					@if(isset($dados['parcerias']))
					@foreach($dados['parcerias'] as $parceria)
					<option value="{{$parceria->id}}">{{$parceria->nome}}</option>
					@endforeach
					@endif
				</select> 
			</div>
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Periodicidade
			</label>
			<div class="col-sm-6"> 
				<select class="c-select form-control boxed" name="periodicidade" required>
					<option>Selecione o período da turma</option>
					<option value="mensal">Mensal</option>
					<option value="bimestral">Bimestral</option>
					<option value="trimestral">Trimestral</option>
					<option value="semestral" selected="selected">Semestral</option>
					<option value="anual">Anual</option>
					<option value="eventual">Eventual</option>
					<option value="ND">Não Definido</option>
		
				</select> 
			</div>
		</div>

		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Dia(s) semana.
			</label>
			<div class="col-sm-6"> 
				
				<label><input class="checkbox" name="dias[]" value="seg" type="checkbox"><span>Seg</span></label>
				<label><input class="checkbox" name="dias[]" value="ter" type="checkbox"><span>Ter</span></label>
				<label><input class="checkbox" name="dias[]" value="qua" type="checkbox"><span>Qua</span></label>
				<label><input class="checkbox" name="dias[]" value="qui" type="checkbox"><span>Qui</span></label>
				<label><input class="checkbox" name="dias[]" value="sex" type="checkbox"><span>Sex</span></label>
				<label><input class="checkbox" name="dias[]" value="sab" type="checkbox"><span>Sab</span></label>
			</div>
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Data de início
			</label>
			<div class="col-sm-2"> 
				
					<input type="date" class="form-control boxed" name="dt_inicio" placeholder="dd/mm/aaaa" required> 
				
			</div>
			<label class="col-sm-2 form-control-label text-xs-right">
				Data do termino
			</label>
			<div class="col-sm-2"> 
				
					<input type="date" class="form-control boxed" name="dt_termino" placeholder="dd/mm/aaaa" required> 
				
			</div>
		</div>

		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Horário de início
			</label>
			<div class="col-sm-2"> 
				<input type="time" class="form-control boxed" name="hr_inicio" placeholder="00:00" required > 
			</div>
			<label class="col-sm-2 form-control-label text-xs-right">
				Horário Termino
			</label>
			<div class="col-sm-2"> 
				<input type="time" class="form-control boxed" name="hr_termino" placeholder="00:00" required> 
			</div>
		</div>
		
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Vagas Ofertadas
			</label>
			<div class="col-sm-2"> 
				<input type="number" class="form-control boxed" name="vagas" placeholder="Recomendado: 30 vagas"> 
			</div>
			<label class="col-sm-2 form-control-label text-xs-right">
				Carga Horária
			</label>
			<div class="col-sm-2"> 
					 
					<input type="number" class="form-control boxed" name="carga" placeholder="" required> 
				
			</div>
		</div>
		<div class="subtitle-block">
			<br>
			<br>
			<h3 class="title">Dados financeiros</h3>
		
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Valor
			</label>
			<div class="col-sm-2"> 
				<div class="input-group">
					<span class="input-group-addon">R$ </span> 
					<input type="text" class="form-control boxed" name="valor" placeholder="Valor TOTAL" required> 
				</div>
			</div>
			<label class="col-sm-2 form-control-label text-xs-right">
				Parcelas
			</label>
			<div class="col-sm-2"> 
				<div class="input-group">
					<input type="number" class="form-control boxed" name="parcelas" value="1" required> 
				</div>
			</div>
			
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">Turma mista</label>
            <div class="col-sm-2"> 		
				<div>
					<label>
						<input class="checkbox" name="mista" type="checkbox" value="true">
						<span title="Turma mista com alunos EMG">Mista EMG</span>
						</label>
				</div>
						
        	</div>
			<label class="col-sm-2 form-control-label text-xs-right">Vagas EMG</label>
            <div class="col-sm-2"> 		
				
					
						<input type="number" class="form-control boxed" name="vagas_emg" placeholder="" > 
						
				
						
        	</div>
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">Pacotes Cursos</label>
            <div class="col-sm-3"> 		
				@foreach($pacote_cursos as $pacote)
				<div>
					<label>
						<input class="checkbox" name="pacote[]" type="checkbox" value="{{$pacote->id}}">
						<span title="{{$pacote->descricao}}">{{$pacote->nome}}</span>
						</label>
				</div>
				@endforeach			
        	</div>
			<label class="col-sm-3 form-control-label text-xs-right"><small>*Capacidade de atendimento definido no cadastro do curso</small></label>
            
		</div>
		<div class="subtitle-block">
			<br>
			<br>
			<h3 class="title">Dados diversos</h3>
		
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Professor Extra
			</label>
			<div class="col-sm-6"> 
				<select class="c-select form-control boxed" name="professor_extra">
					<option>Selecione um professor</option>
					@if(isset($dados['professores']))
					@foreach($dados['professores'] as $professor)
					<option value="{{$professor->id}}">{{$professor->nome}}</option>
					@endforeach
					@endif
				</select> 
			</div>
		</div>
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">
				Próxima turma
			</label>
			<div class="col-sm-2"> 
				<input type="number" class="form-control boxed" name="proxima_turma" placeholder="Código" title="Digite o código caso já houver turma de continuação definida para rematrícula"> 
			</div>
		</div>
		
		<div class="form-group row"> 
			<label class="col-sm-2 form-control-label text-xs-right">Requisitos </label>
            <div class="col-sm-6"> 
            	@foreach($requisitos as $requisito)
				<div>
					<label>
					<input class="checkbox" type="checkbox" name="requisito[]" value="{{$requisito->id}}">
					<span>{{$requisito->nome}}</span>
					</label>
				</div>
				@endforeach
        	</div>
			
                
        </div>
		

            
		<div class="form-group row">
			<div class="col-sm-10 col-sm-offset-2">
				<button type="submit" name="btn" value="1" class="btn btn-primary">Cadastrar</button> 
				<button type="submit" name="btn" value="2" href="disciplinas_show.php?" class="btn btn-secondary">Cadastrar a próxima</button> 
				<!-- 
				<button type="submit" class="btn btn-primary"> Cadastrar</button> 
				-->
			</div>
       </div>
    </div>
    {{csrf_field()}}
</form>
        
@endsection
@section('scripts')
<script type="text/javascript">
$(document).ready(function() 
	{
 
   //On pressing a key on "Search box" in "search.php" file. This function will be called.
 
   $("#fcurso").keyup(function() {
   		
   		$('#fmodulo').val('1');
   		$('#row_modulos').hide();
   		var disciplina = $("input[name=disciplina]").val('');
   		$("#fdisciplina").val('');
   		$('#row_disciplina').hide();

 
       //Assigning search box value to javascript variable named as "name".
 
       var name = $('#fcurso').val();
       var namelist="";

 
       //Validating, if "name" is empty.
 
       if (name == "") {
 
           //Assigning empty value to "display" div in "search.php" file.
 
           $("#listacursos").html("");
 
       }
 
       //If name is not empty.
 
       else {
 
           //AJAX is called.
 			$.get("{{asset('cursos/listarporprogramajs/')}}"+"/"+name)
 				.done(function(data) 
 				{
 					$.each(data, function(key, val){
 						namelist+='<li class="item item-list-header hidden-sm-down">'
 									+'<a href="#" onClick="cursoEscolhido(\''+val.id+'\',\''+val.nome+'\')">'
 										+val.id+' - '+val.nome
 									+'</a>'
 								  +'</li>';
 					});
 					//console.log(namelist);
 					$("#listacursos").html(namelist).show();
 				});
       }
 
  	});
   $("#fdisciplina").keyup(function() {
  
   		
   	
 
       //Assigning search box value to javascript variable named as "name".
 
       var name = $('#fdisciplina').val();
       var namelist="";
       var curso = $("input[name=curso]").val();
       var disciplina = $("input[name=disciplina]").val('');



       if (curso<=0){
       	alert("escolha um curso");
       }
       	

 
       //Validating, if "name" is empty.
 
       if (name == "") {
 
           //Assigning empty value to "display" div in "search.php" file.
 
           $("#listadisciplinas").html("");
 
       }
 
       //If name is not empty.
 
       else {
 
           //AJAX is called.
 			$.get("{{asset('cursos/disciplinas/grade/')}}"+"/"+curso+"/"+name)
 				.done(function(data) 
 				{
 					$.each(data, function(key, val){
 						namelist+='<li class="item item-list-header hidden-sm-down">'
 									+'<a href="#" onClick="disciplinaEscolhida(\''+val.id+'\',\''+val.nome+'\')">'
 										+val.id+' - '+val.nome
 									+'</a>'
 								  +'</li>';
 					

 					});
 					//console.log(namelist);
 					$("#listadisciplinas").html(namelist).show();



 				});

 				/*
 				<option value="324000000000 Adauto Junior 10/11/1984 id:0000014">
					<option value="326500000000 Fulano 06/07/1924 id:0000015">
					<option value="3232320000xx Beltrano 20/02/1972 id:0000016">
					<option value="066521200010 Ciclano 03/08/1945 id:0000017">
					*/
 			
 			
 
       }
 
  	});
 
});
function cursoEscolhido(id,nome){
	$("#listacursos").hide();
	$("#fcurso").val(id +' - '+nome);
	$("input[name=curso]").val(id);
/*
	$.get("{{asset('/pedagogico/curso/modulos/')}}"+"/"+id)
		.done(function(data) {
			//console.log(data);
			if(data>1){
				$('#row_modulos').show();
				$('#fmodulo').attr('max',data);
			}
		});*/
	$.get("{{asset('cursos/disciplinas/grade')}}"+"/"+id)
		.done(function(data) {
			
			if(data.length>0){
				$('#row_disciplina').show();
			}
		});

}

function disciplinaEscolhida(id,nome){
	$("#fdisciplina").val(id +' - '+nome);
	$("input[name=disciplina]").val(id);
	$('#listadisciplinas').hide();

}
function carregarSalas(local){
	var salas;
	$("#select_sala").html('<option>Sem salas cadastradas</option>');
	$.get("{{asset('services/salas-api/')}}"+"/"+local)
 				.done(function(data) 
 				{
 					$.each(data, function(key, val){
						console.log(val.nome);
 						salas+='<option value="'+val.id+'">'+val.nome+'</option>';
 					});
 					//console.log(namelist);
 					$("#select_sala").html(salas);
				 });
				 
}
/* ao selecionar a unidade mostra as salas
$("select[name=unidade]").change( function(){
	var salas='<option selected>Selecione a Sala</option>';
	$("select[name=local]").html('');
	$.get("{{asset('administrativo/salasdaunidade/')}}"+"/"+$("select[name=unidade]").val())
 				.done(function(data) 
 				{
 					$.each(data, function(key, val){
 						salas+='<option value="'+val.id+'">'+val.sala+'</option>';
 					});
 					//console.log(namelist);
 					$("select[name=local]").html(salas).show();
 				})
	

	
	});*/

	


</script>


@endsection
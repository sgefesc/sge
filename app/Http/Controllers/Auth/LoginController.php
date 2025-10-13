<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;// coloca classe email
use App\Mail\recuperarSenha;
use App\Models\Pessoa;
use App\Models\RecursoSistema;
use App\Models\PessoaDadosAcesso;
use App\Models\PessoaDadosContato;
use App\Models\ControleAcessoRecurso;
use App\classes\Strings;
use App\classes\Data;
use Auth;


class LoginController extends Controller
{
    
	public static function login(){
		return view('login');
	}
	
    public static function autorizarDadosPessoais($pessoa){
    	$pessoa=Pessoa::find($pessoa);
		// Verifica se a pessoa existe
		if(!$pessoa)
			return False;

		// Verifica se o perfil não é proprio
		if($pessoa->id != Auth::user()->pessoa)
		{
		//verifica se pode ver outras pessoas
			if(!in_array('4', Auth::user()->recursos))			
				return false;	
		// verifica se a pessoa tem relação institucional
			$relacao_institucional=$pessoa->dadosAdministrativos->where('dado', 16)->count();
			if($relacao_institucional && !in_array('3', Auth::user()->recursos))
			{
				return false;	
			}
		// Verifica se a pessoa tem perfil privado.
			$pessoa_restrita= $pessoa->dadosGerais->where('dado',17)->count();
			if($pessoa_restrita && !in_array('6', Auth::user()->recursos))
				return false;
		// ja verifiquei tudo pode liberar
			return True;	

		}
		else
			return True;
    }

	public function trocarMinhaSenha_view()
	{
		
		return view('auth.passwords.trocar-senha');
	}

	
	//Efetua a troca da senha 
	public function trocarMinhaSenha_exec(Request $r)
	{
		
	
		$this->validate($r , [
			'novasenha'=>'required|between:8,25',
			'confirmanovasenha'=>'required|same:novasenha'
		]);
		$usuario=PessoaDadosAcesso::where('pessoa', Auth::user()->pessoa)->first();
		if(empty($usuario)){
			$erros_bd= ['Erro ao carregar dados de usuário.'];
			return view('auth.passwords.trocar-senha', compact('erros_bd'));
		}
		$credentials = $r->only('username', 'password');
		if(Auth::attempt($credentials))
		{
			$usuario->password = bcrypt($r->novasenha);
			$usuario->save();
			LogController::registrar('pessoa',$usuario->pessoa,'Senha de acesso modificada pelo usuário', Auth::user()->pessoa);

			return redirect('home')->with("dados['alert_sucess']",'Senha redefinida com sucesso.');
		}
		else
		{	
			$erros_bd= ['Senha anterior incorreta.'];
			return view('auth.passwords.trocar-senha', compact('erros_bd'));
		}
	}

	/**
	 * Mostra formulário de adicionar usuario ao sistema
	 * @param Integer id da pessoa que se quer criar o acesso */
	public function cadastrarAcesso_view($p)	
	{
		$pessoa=Pessoa::find($p);
		if(empty($pessoa))
		{
			$erros_bd= ['Código de pessoa inválido'];
			return view('pessoa.cadastrar-acesso', compact('erros_bd'));
		}
		$acesso=PessoaDadosAcesso::where('pessoa', $p)->first();
		if(!empty($acesso))
		{
			$erros_bd= ['Esta pessoa já possui login: '.$acesso->usuario];
			return view('pessoa.cadastrar-acesso', compact('erros_bd'));

		}
		
		$pessoa->nome=Strings::converteNomeParaUsuario($pessoa->nome);
		return view('pessoa.cadastrar-acesso',compact('pessoa'));
	}

	/**
	 * Grava usuário no sistema */	 
	public function cadastrarAcesso_exec(Request $request)
	{
		$this->validate($request, [
			'nome_usuario'=>'required|between:4,20',
			'email' => 'required|email',
			'senha'=>'required|between:8,20',
			'repetir_senha'=>'required|same:senha',
			]);
		$acesso=PessoaDadosAcesso::where('username', $request->nome_usuario)->get();
		if($acesso->count()>0)
		{
			$erros_bd= ['Este nome de usuário já está em uso. '];
			return view('pessoa.cadastrar-acesso', compact('erros_bd'));
		}
		$novo=new PessoaDadosAcesso;

		$novo->username=mb_convert_case($request->nome_usuario, MB_CASE_LOWER, 'UTF-8');
		$novo->password = bcrypt($request->senha);
		$novo->email = $request->email;
		$novo->status=1;
		$novo->pessoa=$request->pessoa;
		if(!isset($request->validade))				
			$novo->validade=date('Y').'-12-31';
		else
			$novo->validade=$request->validade;
		$novo->save();

		$dados=['alert_sucess'=>['Usuario cadastrado com sucesso']];
		return view('pessoa.cadastrar-acesso', compact('dados'));
	}

	public function trocarSenhaUsuario_view($usuario)
	{
		
		$pessoa=Pessoa::find($usuario);
		if(empty($pessoa))
		{
			$erros_bd= ['Código de pessoa inválido'];
			return view('pessoa.cadastrar-acesso', compact('erros_bd'));
		}
		$acesso=PessoaDadosAcesso::where('pessoa', $usuario)->first();
		if(empty($acesso))
		{
			$erros_bd= ['Este nome de usuário ainda não possui Login'];
			return view('pessoa.trocar-senha-usuario', compact('erros_bd'));
		}
		if(!in_array('9', Auth::user()->recursos))
		{
			$erros_bd= ['Desculpe, você não tem permissão para alterar senha de outras pessoas'];
			return view('pessoa.cadastrar-acesso', compact('erros_bd'));
		}
		$relacao_institucional=$pessoa->dadosAdministrativos->where('dado', 16)->count();
		if($relacao_institucional && !in_array('10', Auth::user()->recursos))
		{
			$erros_bd= ['Desculpe, você não tem permissão para alterar senha de pessoas ligadas à FESC'];
			return view('pessoa.cadastrar-acesso', compact('erros_bd'));	
		}
		$pessoa_restrita= $pessoa->dadosGerais->where('dado',17)->count();
		if($pessoa_restrita && !in_array('11', Auth::user()->recursos))
		{
			$erros_bd= ['Desculpe, você não tem permissão para alterar senha de pessoas restritas'];
			return view('pessoa.cadastrar-acesso', compact('erros_bd'));
		}
		return view('pessoa.trocar-senha-usuario', compact('pessoa'));
	}

	public function trocarSenhaUsuario_exec(Request $request)
	{
		
		$this->validate($request, [
			'pessoa'=>'required|integer',
			'nova_senha'=>'required|between:8,25',
			'repetir_senha'=>'required|same:nova_senha'
			]);


		$acesso=PessoaDadosAcesso::where('pessoa', $request->pessoa)->first();
		
		if(empty($acesso))
		{
			$erros_bd= ['Este nome de usuário ainda não possui Login'];
			return view('pessoa.trocar-senha-usuario', compact('erros_bd'));
		}
		$pessoa = Pessoa::find($acesso->pessoa);

		if(!in_array('9', Auth::user()->recursos))
		{
			$erros_bd= ['Desculpe, você não tem permissão para alterar senha de outras pessoas'];
			return view('pessoa.trocar-senha-usuario', compact('erros_bd'));
		}
		$relacao_institucional=$pessoa->dadosAdministrativos->where('dado', 16)->count();
		if($relacao_institucional && !in_array('10', Auth::user()->recursos))
		{
			$erros_bd= ['Desculpe, você não tem permissão para alterar senha de pessoas ligadas à FESC'];
			return view('pessoa.trocar-senha-usuario', compact('erros_bd'));	
		}
		$pessoa_restrita= $pessoa->dadosGerais->where('dado',17)->count();
		if($pessoa_restrita && !in_array('11', Auth::user()->recursos))
		{
			$erros_bd= ['Desculpe, você não tem permissão para alterar senha de pessoas restritas'];
			return view('pessoa.trocar-senha-usuario', compact('erros_bd'));
		}
		if(!isset($request->validade))				
			$acesso->validade=date('Y').'-12-31';
		else
		{
			$this->validate($request,[
				'validade'=>'date'
				]);
			$acesso->validade=$request->validade;
		}
		$acesso->password=bcrypt($request->nova_senha);
		$acesso->save();
		$dados=['alert_sucess'=>['Senha alterada com sucesso!']];
		LogController::registrar('pessoa',$acesso->pessoa,'Senha de acesso modificada por terceiro', Auth::user()->pessoa);


		return view('pessoa.trocar-senha-usuario', compact('dados'));

	}


	public function listarUsuarios_view(Request $r)
	{
		if(!is_null($r))
		$dados=$this->listarUsuarios_data($r->buscar);
		else
		$dados=$this->listarUsuarios_data(null);
		return view('admin/listarusuarios', compact('dados'));	
	}


	public function listarUsuarios_data($r = '')
	{

		if($r=='')
			$usuarios=PessoaDadosAcesso::orderBy('username','ASC')->paginate(50);
		else
			$usuarios=PessoaDadosAcesso::where('username', 'like', '%'.$r.'%')->orderBy('username','ASC')->paginate(50);
		
		$dados=['usuarios'=> $usuarios];

		foreach($dados['usuarios'] as $usuario)
		{
			if(strtotime($usuario->validade) < strtotime(date('Y-m-d')))
				$usuario->status=2;

			switch($usuario->status){
				case 0:
				$usuario->status="Bloqueado";
				break;			
				case 1:
				$usuario->status="Ativado";
				break;
				case 2:
				$usuario->status="Vencido";
				break;


			}
			$usuario->nome=Pessoa::getNome($usuario->pessoa);
			$usuario->validade=Data::converteParaUsuario($usuario->validade);
		}
		return $dados;
		
	}

	

	public function alterar($acao,$itens)
	{
		if(!isset(Auth::user()->id))
			return redirect(asset("/"));

		if(!in_array('9', Auth::user()->recursos))
		{
			$erros_bd= ['Desculpe, você não tem permissão para alterar dados de acesso de outras pessoas.'];
			return view('admin.listarusuarios', compact('erros_bd'));
		}


		$logins=explode(',',$itens);
		//$items=array_pop($logins);
		
		$filtered_login=[];
		foreach($logins as $l){
			if(is_numeric($l))
				array_push($filtered_login,$l);
		}

		switch($acao)
		{
			case 1: // Renovar a validade
				foreach ($filtered_login as $id_acesso){
					$acesso=PessoaDadosAcesso::find($id_acesso);
					if(!$acesso)
						return view('error-404-alt')->with(array('error'=>['id'=>'404','desc'=>'Código de pessoa não encontrado. LoginController(442) ']));
					$pessoa=Pessoa::find($acesso->pessoa);
					if(!$pessoa)
						return view('error-404-alt')->with(array('error'=>['id'=>'404','desc'=>'Código de pessoa não encontrado. LoginController(445) ']));
					$relacao_institucional=$pessoa->dadosAdministrativos->where('dado', 16)->count();
					if($relacao_institucional && !in_array('10', Auth::user()->recursos))
					{
						$dados['alert_warning'][]='Desculpe, você não tem permissão para alterar: '.$acesso->usuario.' por ser uma pessoa com relação institucional.';	
							
					}
					$pessoa_restrita= $pessoa->dadosGerais->where('dado',17)->count();
					if($pessoa_restrita && !in_array('11', Auth::user()->recursos))
					{
						$dados['alert_warning'][]='Desculpe, você não tem permissão para alterar: '.$acesso->usuario.' por se tratar de uma pessoa de acesso restrito.';
						
					}
					
					$acesso->validade=date('Y').'-12-31';
					$acesso->save();
					$dados['alert_sucess'][]= $acesso->usuario." alterado com sucesso.";


				}
				$dados=array_merge($dados,$this->listarUsuarios_data());
				LogController::registrar('pessoa',$acesso->pessoa,'Acesso ao sistema renovado', Auth::user()->pessoa);

				return view('admin.listarusuarios', compact('dados'));
			break;
			case 2: // Ativar acesso
				foreach ($filtered_login as $id_acesso){
					$acesso=PessoaDadosAcesso::find($id_acesso);
					if(!$acesso)
						return view('error-404-alt')->with(array('error'=>['id'=>'404','desc'=>'Código de pessoa não encontrado. LoginController(742) ']));
					$pessoa=Pessoa::find($acesso->pessoa);
					if(!$pessoa)
						return view('error-404-alt')->with(array('error'=>['id'=>'404','desc'=>'Código de pessoa não encontrado. LoginController(475) ']));
					$relacao_institucional=$pessoa->dadosAdministrativos->where('dado', 16)->count();
					if($relacao_institucional && !in_array('10', Auth::user()->recursos))
					{
						$dados['alert_warning'][]='Desculpe, você não tem permissão para alterar: '.$acesso->login.' por ser uma pessoa ligada à FESC';	
					}
					$pessoa_restrita= $pessoa->dadosGerais->where('dado',17)->count();
					if($pessoa_restrita && !in_array('11', Auth::user()->recursos))
					{
						$dados['alert_warning'][]='Desculpe, você não tem permissão para alterar: '.$acesso->login.' por se tratar de uma pessoa de acesso restrito';
					}
					$acesso->status=1;
					$acesso->save();
					$dados['alert_sucess'][]=$acesso->usuario." alterado com sucesso";
				}
				$dados=array_merge($dados,$this->listarUsuarios_data());
				LogController::registrar('pessoa',$acesso->pessoa,'Acesso ao sistema ativado', Auth::user()->pessoa);

				return view('admin.listarusuarios', compact('dados'));
			break;
			case 3: // desativar acesso
				foreach ($filtered_login as $id_acesso)
				{
					$acesso=PessoaDadosAcesso::find($id_acesso);
					if(!$acesso)
						return view('error-404-alt')->with(array('error'=>['id'=>'404','desc'=>'Código de pessoa não encontrado. LoginController(499) ']));
					$pessoa=Pessoa::find($acesso->pessoa);
					if(!$pessoa)
						return view('error-404-alt')->with(array('error'=>['id'=>'404','desc'=>'Código de pessoa não encontrado. LoginController(502) ']));
					$relacao_institucional=$pessoa->dadosAdministrativos->where('dado', 16)->count();
					if($relacao_institucional && !in_array('10', Auth::user()->recursos))
					{
						$dados['alert_warning'][]='Desculpe, você não tem permissão para alterar: '.$acesso->login.' por ser uma pessoa ligada à FESC';	
					}
					$pessoa_restrita= $pessoa->dadosGerais->where('dado',17)->count();
					if($pessoa_restrita && !in_array('11', Auth::user()->recursos))
					{
						$dados['alert_warning'][]='Desculpe, você não tem permissão para alterar: '.$acesso->login.' por se tratar de uma pessoa de acesso restrito';
					}
					$acesso->status=0;
					$acesso->save();
					$dados['alert_sucess']=[$acesso->usuario." alterado com sucesso"];
					}
				$dados=array_merge($dados,$this->listarUsuarios_data());
				LogController::registrar('pessoa',$acesso->pessoa,'Acesso ao sistema desativado', Auth::user()->pessoa);

				return view('admin.listarusuarios', compact('dados'));
				break;
		}// end switch
	}//end alterar()
	public function credenciais_view($id,$msg=''){
		$pessoa=Pessoa::find($id);
		if(!$pessoa)
			return view('error-404-alt')->with(array('error'=>['id'=>'404','desc'=>'Código de pessoa não encontrado. LoginController(525) ']));
		$recursos_usuario=ControleAcessoRecurso::where('pessoa',$id)->get();
		$dados=RecursoSistema::select('*')->orderBy('desc')->get();
		foreach($dados as $recurso){
			foreach($recursos_usuario as $recurso_usuario){
				if($recurso_usuario->recurso==$recurso->id)
					$recurso->checked='checked';
			}

		}
		//return $dados;
		$pessoa->alert_sucess=$msg;


		return view('gestaopessoal.credenciais', compact('dados'))->with('pessoa',$pessoa);

	}
	public function credenciais_exec(Request $request){

		$login=PessoaDadosAcesso::where('pessoa',$request->pessoa);
		if(!$login)
			return view('error-404-alt')->with(array('error'=>['id'=>'404','desc'=>'Nenhum login vinculado à essa pessoa. LoginController(545) ']));
		$recursos_atuais=ControleAcessoRecurso::where('pessoa', $request->pessoa)->get();
		foreach($recursos_atuais->all() as $recurso_atual){
			$recurso_atual->delete();
		}

		if(is_array($request->recurso)){
			foreach($request->recurso as $item){
				$novo_recurso= new ControleAcessoRecurso;
				$novo_recurso->timestamps=false;
				$novo_recurso->pessoa=$request->pessoa;
				$novo_recurso->recurso=$item;
				$novo_recurso->save();
			}
		}
		LogController::registrar('pessoa',$request->pessoa,'Alteração de credenciais', Auth::user()->pessoa);

		

		return $this->credenciais_view($request->pessoa,'Credenciais atualizadas' );

	}
	public function sendNewPassword(){
		$users = \App\Models\PessoaDadosAcesso::where('status',1)->get();
		foreach($users as $user){
			$password = date('Y').'Fsc'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
			$user->password = bcrypt($password);
			$user->save();
			if(!empty($user->email)){
				LogController::registrar('pessoa',$acesso->pessoa,'Acesso ao sistema desativado', Auth::user()->pessoa);

				Mail::send('emails.default', ['username' => $user->username , 'password' => $password], function ($message) use($user){
					$message->from('sge@fesc.app.br', 'Sistema Fesc');
					$message->to($user->email);
					$message->subject('Atualização de senha');
					});
                $emails[] = $user->email;
					
			}
			
		}
			
	

		

		/*
		Mail::to($user->email)->send(new defaultMailSender->with());
		  Mail::send('emails.default', ['content' => '', 'logo' =>'',' title' => '', 'branch_name' => ''], function ($message) use ($subject, $to){
			$message->from('From Email Address', 'Mail Title');
			$message->to('Sender Email Address');
			$message->subject('Email Subject');
			});
	*/
		
		return $emails;
	}

	public function useAs(int $id){
		$teste = Auth::loginUsingId($id);
		dd(Auth::user()->pessoa);
		return redirect('home');
	}

}

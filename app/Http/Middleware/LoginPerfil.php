<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Pessoa;
use App\Models\PessoaDadosContato;

class LoginPerfil
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(session('pessoa_perfil') == null)
            return redirect('/perfil/cpf');
        else{

            $pessoa = Pessoa::find(session('pessoa_perfil'));
            if($pessoa == null)
                return redirect('/perfil/cpf')->withErrors(['Pessoa não encontrada!']);
            else 
                $request->pessoa = $pessoa;
            
            $email = PessoaDadosContato::where('pessoa',$request->pessoa->id)->where('dado',1)->orderbyDesc('id')->first();
            if($email != null)
                $request->pessoa->email = $email->valor;
            else
                $request->pessoa->email = null;

            $request->pessoa->celular = $request->pessoa->getCelular();        

            return $next($request);

        }

    }
}

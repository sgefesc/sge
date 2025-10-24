<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\TagLog;
use Auth;

class TagController extends Controller
{
    public function index($pessoa=null){
        if($pessoa){
            $pessoa = \App\Models\Pessoa::find($pessoa);
            $tags = Tag::where('pessoa',$pessoa->id)->get();
            $logs = TagLog::whereIn('tag',$tags->pluck('id')->toArray())->orderByDesc('id')->paginate(100);
        }
        else{     
            $tags = Tag::paginate(30);
            $logs = TagLog::where('id',0)->paginate(30);
        }
        //dd($tags);

        return view('tags.index')
            ->with('pessoa',$pessoa)
            ->with('tags',$tags)
            ->with('logs',$logs);

    }

    public function gerenciar(Request $request){
        if($request->has('nome')){
            $pessoas = \App\Models\Pessoa::where('nome','like','%'.$request->nome.'%')->get();
            $tags = Tag::whereIn('pessoa',$pessoas->pluck('id')->toArray())->paginate(30); 
            return view('tags.admin',compact('tags'));    
        }
        $tags = Tag::paginate(30);
        return view('tags.admin',compact('tags'));
    }

    public function criar(Request $r){
   
        if($r->tag>0){
            $tag = Tag::where('tag',str_pad($r->tag,20,'0',STR_PAD_LEFT))->first();
            if($tag)
                return redirect()->back()->with(['warning'=>'Tag já cadastrada']);
            
            $tag = new Tag;
            $tag->pessoa = $r->pessoa;
            $tag->tag = str_pad($r->tag,20,'0',STR_PAD_LEFT);
            $tag->data = new \DateTime();
            $tag->responsavel = Auth::user()->pessoa;
            $tag->save();

            TagLog::cadastrar($tag->id);
           
            return redirect()->back()->with(['success'=>'Tag cadastrada']);
        }
        else
            return redirect()->back()->with(['danger'=>'Tag inválida']);

       

    }


    public function apagar($id){
        $tag = Tag::destroy($id);
        $log = new TagLog;
        $log->tag = $id;
        $log->evento = 'exclusao_tag';
        $log->data = new \DateTime();
        $log->save();
        return response('ok',200);

    }

    public function addLivreAcesso($id){
        $tag = Tag::find($id);
        if(!$tag)
            return response('Tag inválida',500);
        
        $tag->livre_acesso = 1;
        $tag->save();

        return response('ok',200);
    }
    public function remLivreAcesso($id){
        $tag = Tag::find($id);
        if(!$tag)
           return response('Tag inválida',500);
        
        $tag->livre_acesso = 0;
        $tag->save();

        return response('ok',200);
    }



    public function tagAccess($tag,$key){
        //dd(md5($tag));
        if(md5($tag) !== $key)
            return response('negado: chave invalida',403);
        
        
        $tag = Tag::where('tag',$tag)->first();
        if(!$tag)
            return response('negado: tag invalida',403);
        
        
        $pendencia = \App\Models\PessoaDadosAdministrativos::where('pessoa',$tag->id)->where('dado','pendencia')->first();
        if($pendencia){
            TagLogController::registrar('acesso_piscina_negado',$tag->id);
            return response('negado: pendente',403);
        }
        else{
            TagLogController::registrar('acesso_piscina_liberado',$tag->id);
            return response ('liberado',200);
        }
            

        

    }
}

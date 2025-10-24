<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    //protected $dates = ['data'];
    protected $casts = [
    'data' => 'date',
    ];

     //
    public static function registrar($evento,$tag){
        
        $log = new TagLog;
        $log->tag = $tag;
        $log->evento = $evento;
        $log->data = new \DateTime();
        $log->save();
        return $log;
        
    }

    public static function cadastrar($tag){
        TagLog::registrar('cadastro de tag',$tag);
    }

    public static function excluir($tag){
        TagLog::registrar('exclusão de tag',$tag);
    }

    public static function liberar($tag){
        TagLog::registrar('livre acesso concedido',$tag);
    }

    public static function limitar($tag){
        TagLog::registrar('livre acesso retirado',$tag);
    }
}

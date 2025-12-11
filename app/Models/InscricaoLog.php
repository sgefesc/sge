<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    public static function registrar($inscricao_id, $pessoa_id, $evento){
        $log = new InscricaoLog;
        $log->inscricao = $inscricao_id;
        $log->evento = $evento;
        $log->pessoa = $pessoa_id;
        $log->data = date('Y-m-d H:i:s');
        $log->save();
    }
}

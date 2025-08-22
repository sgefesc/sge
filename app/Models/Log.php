<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    //
    public $timestamps = false;
    //protected $dates = ['data'];
    protected $casts = [
    'data' => 'date',
    ];

    public function getPessoa(){
        $pessoa = \App\Models\Pessoa::withTrashed()->find($this->pessoa);
        return $pessoa->nome_simples;
    }
}

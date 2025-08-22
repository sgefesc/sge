<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurmaLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    //protected $dates = ['data'];
    protected $casts = [
    'data' => 'date',

    ];

    public function getPessoa(){
        $pessoa = \App\Models\Models\Pessoa::withTrashed()->find($this->pessoa);
        return $pessoa->nome_simples;
    }
}

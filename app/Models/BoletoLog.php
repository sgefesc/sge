<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletoLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    //protected $dates = ['data'];
    protected $casts = [
    'data' => 'date',
    ];

    public function getPessoa(){
        $pessoa = \App\Models\Pessoa::find($this->pessoa);
        return $pessoa->nome_simples;
    }

    


}

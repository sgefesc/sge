<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    public $timestamps = false;
    use SoftDeletes;
    //protected $dates = ['data','deleted_at'];
    protected $casts = [
    'data' => 'date',
	'deleted_at' => 'date',
    ];

    public function getNomePessoa(){
        $p = \App\Models\Pessoa::find($this->pessoa);
        if($p)
            return $p->nome;
        else
            return 'Pessoa não encontrada';
    }
}

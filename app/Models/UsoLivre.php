<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsoLivre extends Model
{
    use HasFactory;
    public $timestamps = false;
    //protected $dates = ['inicio'];
    protected $casts = [
    'inicio' => 'date',
    ];

    public function getUsuario(){
        $pessoa = \App\Models\Pessoa::find($this->atendido);
        return $pessoa->nome;
    }
    public function getResponsavel(){
        $pessoa = \App\Models\Pessoa::find($this->responsavel);
        return $pessoa->nome;

    }
}

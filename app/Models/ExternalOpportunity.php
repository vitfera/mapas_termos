<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalOpportunity extends Model
{
    // Usa a conexão pgsql_remote
    protected $connection = 'pgsql_remote';

    // Nome da tabela no Postgres
    protected $table = 'opportunity';

    // Se o nome da PK for outro, defina aqui; por padrão é 'id'
    // protected $primaryKey = 'id';

    // Se não for usar timestamps (created_at/updated_at)
    public $timestamps = false;

    // Quais campos são fillable, se você for criar registros via Eloquent
    protected $fillable = [
        'id', 'name', 'parent_id',
    ];
}

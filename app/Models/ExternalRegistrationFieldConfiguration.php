<?php

// app/Models/ExternalRegistrationFieldConfiguration.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalRegistrationFieldConfiguration extends Model
{
    protected $connection = 'pgsql_remote';
    protected $table      = 'registration_field_configuration';
    public    $timestamps = false;

    // Se quiser, mapeie apenas os campos que usa:
    protected $fillable = [
        'id', 'opportunity_id', 'key', 'title', 'display_order'
    ];
}

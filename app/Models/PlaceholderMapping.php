<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceholderMapping extends Model
{
    protected $fillable = [
        'placeholder_key',
        'placeholder_label',
        'opportunity_id',
        'field_id',
        'priority',
    ];

    // Relação com a oportunidade (Postgres remoto)
    public function opportunity()
    {
        return $this->belongsTo(
            \App\Models\ExternalOpportunity::class,
            'opportunity_id'
        );
    }

    // Relação com o campo dinâmico (Postgres remoto)
    public function field()
    {
        return $this->belongsTo(
            \App\Models\ExternalRegistrationFieldConfiguration::class,
            'field_id'
        );
    }
}

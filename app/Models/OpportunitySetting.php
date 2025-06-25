<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ExternalOpportunity;

class OpportunitySetting extends Model
{
    protected $fillable = [
        'opportunity_id',
        'category',
        'start_number',
        'last_sequence',
    ];

    /**
     * Relação com edital externo
     */
    public function opportunity()
    {
        return $this->belongsTo(ExternalOpportunity::class, 'opportunity_id');
    }
}
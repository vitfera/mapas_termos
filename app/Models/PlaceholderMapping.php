<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceholderMapping extends Model
{
    protected $fillable = [
        'placeholder_id',
        'opportunity_id',
        'source_type',
        'source_key',
        'priority',
    ];
}

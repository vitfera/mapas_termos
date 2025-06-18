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

    /**
     * The placeholder this mapping applies to.
     */
    public function placeholder(): BelongsTo
    {
        return $this->belongsTo(TemplatePlaceholder::class, 'placeholder_id');
    }
}

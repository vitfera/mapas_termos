<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplatePlaceholder extends Model
{
    protected $fillable = [
        'template_id',
        'key',
        'label',
    ];

    /**
     * The template this placeholder belongs to.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * The mappings that connect this placeholder to DB fields per opportunity.
     */
    public function mappings(): HasMany
    {
        return $this->hasMany(PlaceholderMapping::class, 'placeholder_id');
    }
}

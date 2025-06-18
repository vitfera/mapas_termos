<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'header_html',
        'footer_html',
        'body_html',
    ];

}

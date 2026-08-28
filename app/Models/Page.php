<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'template',
        'banner_image',
        'content',
        'blocks',
        'show_in_navbar',
        'footer_column',
        'status',
    ];

    protected $casts = [
        'show_in_navbar' => 'boolean',
        'blocks' => 'array',
    ];
}

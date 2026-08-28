<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'highlight_text',
        'description',
        'image',
        'button_text',
        'button_url',
        'category_tag',
        'status',
        'order_column',
    ];
}

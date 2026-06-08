<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomUserPages extends Model
{
    protected $table = 'custom_user_pages';

    protected $fillable = [
        'headline',
        'description',
        'first_image_path',
        'second_image_path'
    ];
}

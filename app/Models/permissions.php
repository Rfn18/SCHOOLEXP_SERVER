<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class permissions extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'guard_name',
    ];
}

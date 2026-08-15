<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'anonymous_code',
        'category',
        'message',
        'name',
    ];

    /**
     * Casting tipe data.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Buat kode anonim unik.
     *
     * Contoh:
     * FB-8H2K-93JD
     */
    public static function generateUniqueAnonymousCode(): string
    {
        do {
            $code = sprintf(
                'FB-%s-%s',
                strtoupper(Str::random(4)),
                strtoupper(Str::random(4))
            );
        } while (self::where('anonymous_code', $code)->exists());

        return $code;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiResponseCache extends Model
{
    protected $fillable = ['file_hash', 'ai_response'];
    protected $casts = [
        'ai_response' => 'array' 
    ];

    protected $table = 'ai_response_caches';
}

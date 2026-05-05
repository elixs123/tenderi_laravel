<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketIntelligence extends Model
{
    protected $table = 'market_intelligence';
    protected $guarded = [];

    protected $casts = [
        'event_date' => 'datetime',
    ];
}

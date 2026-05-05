<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    protected $casts = [
        'raw_data' => 'array',
        'estimated_value' => 'decimal:2',
    ];

    public function workflow()
    {
        return $this->hasOne(TenderWorkflow::class, 'procedure_id', 'id')
                    ->where('user_id', auth()->id());
    }

    public function tasks()
    {
        return $this->hasMany(TenderTask::class);
    }
}



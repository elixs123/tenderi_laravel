<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenderWorkflow extends Model
{
    protected $guarded = [];

    protected $fillable = [
    'procedure_id', 'user_id', 'status', 'reason', 'cancel_reason', 'document_path', 'completed_at', 'ai_parsed_data', 'accepted_lots', 'erp_document_id', 'winner_supplier',
    'final_price',
    'won_at',
    'lost_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'ai_parsed_data' => 'array',
        'accepted_lots' => 'array'
    ];

    public function tasks()
    {
        return $this->hasMany(TenderTask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lots()
    {
        return $this->hasMany(Lot::class, 'procedure_id', 'procedure_id');
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id', 'id');
    }
}

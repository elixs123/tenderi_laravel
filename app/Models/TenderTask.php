<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_workflow_id',
        'naziv',
        'kategorija',
        'status',
        'razlog_kasnjenja',
        'acquired_at',
        'file_name',
        'file_path',
        'completed_at'
    ];

    protected $table = "tender_tasks";

    protected $casts = [
        'acquired_at' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(TenderWorkflow::class);
    }

}
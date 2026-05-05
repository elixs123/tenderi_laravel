<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    protected $guarded = [];

    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'announced' => 'datetime',
        'last_updated' => 'datetime',
        'has_complaint' => 'boolean',
        'has_lots' => 'boolean',
        'is_auction_online' => 'boolean',
        'is_electronic_offer' => 'boolean',
        'is_joint_procurement' => 'boolean',
        'is_master_agreement' => 'boolean',
        'is_on_behalf_procurement' => 'boolean',
        
    ];

    // Relacija prema Lotovima
    public function lots()
    {
        return $this->hasMany(Lot::class, 'procedure_id', 'id');
    }

    public function workflow()
    {
        return $this->hasOne(TenderWorkflow::class, 'procedure_id', 'id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncJobLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'job',
        'status',
        'synced_from',
        'synced_to',
        'started_at',
        'finished_at',
        'inserted_count',
        'updated_count',
        'inserted_ids',
    ];

    protected $casts = [
        'synced_from'  => 'datetime',
        'synced_to'    => 'datetime',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
        'inserted_ids' => 'array',
    ];

    public static function lastCompletedTendersInsertedIds(): array
    {
        return static::where('job', 'tenders')
            ->where('status', 'completed')
            ->latest('synced_to')
            ->value('inserted_ids') ?? [];
    }

    public static function lastSyncedTo(string $job): ?\Carbon\Carbon
    {
        $value = static::where('job', $job)
            ->where('status', 'completed')
            ->latest('synced_to')
            ->value('synced_to');

        return $value ? \Carbon\Carbon::parse($value) : null;
    }
}

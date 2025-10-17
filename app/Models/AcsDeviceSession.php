<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcsDeviceSession extends Model
{
    protected $fillable = [
        'ont_id',
        'session_id',
        'remote_ip',
        'last_inform',
        'last_boot',
        'inform_interval',
        'parameters',
    ];

    protected $casts = [
        'parameters' => 'array',
        'last_inform' => 'datetime',
        'last_boot' => 'datetime',
        'inform_interval' => 'integer',
    ];

    // Relations
    public function ont()
    {
        return $this->belongsTo(ONT::class, 'ont_id', 'id');
    }

    // Helper Methods
    public function isOnline()
    {
        if (!$this->last_inform) {
            return false;
        }

        // Online if informed in last 10 minutes
        return $this->last_inform->gt(now()->subMinutes(10));
    }

    public function getStatusAttribute()
    {
        return $this->isOnline() ? 'online' : 'offline';
    }

    public function getUptimeAttribute()
    {
        if (!$this->last_boot) {
            return null;
        }

        return $this->last_boot->diffForHumans(null, true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcsProvisioningQueue extends Model
{
    protected $table = 'acs_provisioning_queue';

    protected $fillable = [
        'ont_id',
        'olt_id',
        'sn',
        'pon_port',
        'ont_id_number',
        'provision_type',
        'config_data',
        'priority',
        'status',
        'error_message',
        'retry_count',
        'max_retries',
        'scheduled_at',
        'processed_at',
    ];

    protected $casts = [
        'config_data' => 'array',
        'pon_port' => 'integer',
        'ont_id_number' => 'integer',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
        'scheduled_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // Relations
    public function ont()
    {
        return $this->belongsTo(ONT::class);
    }

    public function olt()
    {
        return $this->belongsTo(OLT::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'pending')
            ->where(function($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');
    }

    // Helper Methods
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->increment('retry_count');

        $status = ($this->retry_count >= $this->max_retries) ? 'failed' : 'pending';

        $this->update([
            'status' => $status,
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }

    public function canRetry()
    {
        return $this->retry_count < $this->max_retries;
    }
}

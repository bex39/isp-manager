<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcsBulkOperation extends Model
{
    protected $fillable = [
        'operation_name',
        'operation_type',
        'target_filter',
        'parameters',
        'total_devices',
        'processed_devices',
        'success_count',
        'failed_count',
        'status',
        'progress_percentage',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'target_filter' => 'array',
        'parameters' => 'array',
        'total_devices' => 'integer',
        'processed_devices' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'progress_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(AcsBulkOperationDetail::class, 'bulk_operation_id');
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function getProgressPercentage()
    {
        if ($this->total_devices == 0) {
            return 0;
        }

        return round(($this->processed_devices / $this->total_devices) * 100, 2);
    }

    public function updateProgress()
    {
        $this->progress_percentage = $this->getProgressPercentage();
        $this->save();
    }

    public function incrementProcessed($success = true)
    {
        $this->increment('processed_devices');

        if ($success) {
            $this->increment('success_count');
        } else {
            $this->increment('failed_count');
        }

        $this->updateProgress();

        // Check if completed
        if ($this->processed_devices >= $this->total_devices) {
            $this->markAsCompleted();
        }
    }

    public function markAsStarted()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }

    public function getDuration()
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->completed_at ?? now();
        return $this->started_at->diffForHumans($end, true);
    }

    public function getSuccessRate()
    {
        if ($this->processed_devices == 0) {
            return 0;
        }

        return round(($this->success_count / $this->processed_devices) * 100, 2);
    }
}

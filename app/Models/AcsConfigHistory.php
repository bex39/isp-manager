<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcsConfigHistory extends Model
{
    protected $fillable = [
        'ont_id',
        'template_id',
        'action',
        'parameters',
        'result',
        'status',
        'error_message',
        'executed_by',
        'executed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'result' => 'array',
        'executed_at' => 'datetime',
    ];

    // Relations
    public function ont()
    {
        return $this->belongsTo(ONT::class);
    }

    public function template()
    {
        return $this->belongsTo(AcsConfigTemplate::class, 'template_id');
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function isSuccess()
    {
        return $this->status === 'success';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function markAsSuccess($result = null)
    {
        $this->update([
            'status' => 'success',
            'result' => $result,
            'executed_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'executed_at' => now(),
        ]);
    }
}

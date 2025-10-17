<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcsBulkOperationDetail extends Model
{
    protected $fillable = [
        'bulk_operation_id',
        'ont_id',
        'status',
        'result',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'result' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relations
    public function bulkOperation()
    {
        return $this->belongsTo(AcsBulkOperation::class, 'bulk_operation_id');
    }

    public function ont()
    {
        return $this->belongsTo(ONT::class);
    }

    // Scopes
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

    // Helper Methods
    public function markAsSuccess($result = null)
    {
        $this->update([
            'status' => 'success',
            'result' => $result,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }
}

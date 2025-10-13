<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiber_core_id',
        'test_date',
        'test_type',
        'total_loss',
        'total_length',
        'status',
        'test_data',
        'technician',
        'sor_file',
        'notes',
    ];

    protected $casts = [
        'test_date' => 'date',
        'total_loss' => 'float',
        'total_length' => 'float',
        'test_data' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * FiberTestResult belongs to FiberCore
     */
    public function fiberCore()
    {
        return $this->belongsTo(FiberCore::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if test passed
     */
    public function isPassed()
    {
        return $this->status === 'pass';
    }

    /**
     * Check if test failed
     */
    public function isFailed()
    {
        return $this->status === 'fail';
    }

    /**
     * Check if test has warning
     */
    public function hasWarning()
    {
        return $this->status === 'warning';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pass' => 'badge bg-success',
            'fail' => 'badge bg-danger',
            'warning' => 'badge bg-warning',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get test type badge
     */
    public function getTestTypeBadge()
    {
        return match($this->test_type) {
            'OTDR' => 'badge bg-primary',
            'Power Meter' => 'badge bg-info',
            'Light Source' => 'badge bg-success',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get loss quality
     */
    public function getLossQuality()
    {
        if (!$this->total_loss) {
            return 'unknown';
        }

        if ($this->total_loss <= 3) {
            return 'excellent';
        } elseif ($this->total_loss <= 5) {
            return 'good';
        } elseif ($this->total_loss <= 8) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get formatted length
     */
    public function getFormattedLength()
    {
        if (!$this->total_length) {
            return 'N/A';
        }

        return number_format($this->total_length, 2) . ' km';
    }

    /**
     * Get formatted loss
     */
    public function getFormattedLoss()
    {
        if (!$this->total_loss) {
            return 'N/A';
        }

        return number_format($this->total_loss, 2) . ' dB';
    }
}

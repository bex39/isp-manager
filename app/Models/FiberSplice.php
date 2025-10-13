<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberSplice extends Model
{
    use HasFactory;

    protected $fillable = [
        'joint_box_id',
        'input_segment_id',
        'input_core_number',
        'output_segment_id',
        'output_core_number',
        'splice_type',
        'splice_loss',
        'splice_date',
        'technician',
        'notes',
    ];

    protected $casts = [
        'splice_date' => 'date',
        'splice_loss' => 'float',
        'input_core_number' => 'integer',
        'output_core_number' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * FiberSplice belongs to JointBox
     */
    public function jointBox()
    {
        return $this->belongsTo(JointBox::class);
    }

    /**
     * Input FiberCableSegment
     */
    public function inputSegment()
    {
        return $this->belongsTo(FiberCableSegment::class, 'input_segment_id');
    }

    /**
     * Output FiberCableSegment
     */
    public function outputSegment()
    {
        return $this->belongsTo(FiberCableSegment::class, 'output_segment_id');
    }

    /**
     * Get input FiberCore
     */
    public function inputCore()
    {
        return $this->hasOneThrough(
            FiberCore::class,
            FiberCableSegment::class,
            'id', // Foreign key on fiber_cable_segments
            'cable_segment_id', // Foreign key on fiber_cores
            'input_segment_id', // Local key on fiber_splices
            'id' // Local key on fiber_cable_segments
        )->where('fiber_cores.core_number', $this->input_core_number);
    }

    /**
     * Get output FiberCore
     */
    public function outputCore()
    {
        return $this->hasOneThrough(
            FiberCore::class,
            FiberCableSegment::class,
            'id',
            'cable_segment_id',
            'output_segment_id',
            'id'
        )->where('fiber_cores.core_number', $this->output_core_number);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get splice type badge
     */
    public function getSpliceTypeBadge()
    {
        return match($this->splice_type) {
            'fusion' => 'badge bg-success',
            'mechanical' => 'badge bg-info',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get loss quality status
     */
    public function getLossQuality()
    {
        if ($this->splice_loss <= 0.05) {
            return 'excellent';
        } elseif ($this->splice_loss <= 0.1) {
            return 'good';
        } elseif ($this->splice_loss <= 0.2) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get loss badge class
     */
    public function getLossBadgeClass()
    {
        $quality = $this->getLossQuality();

        return match($quality) {
            'excellent' => 'badge bg-success',
            'good' => 'badge bg-info',
            'fair' => 'badge bg-warning',
            'poor' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }
}

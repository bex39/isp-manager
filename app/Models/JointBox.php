<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JointBox extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'latitude',
        'longitude',
        'address',
        'capacity',
        'used_capacity',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'used_capacity' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * JointBox has many FiberSplices
     */
    public function splices()
{
    return $this->hasMany(FiberSplice::class, 'joint_box_id');
}

    /**
     * JointBox has many FiberCableSegments (as start point)
     */
    public function outgoingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'start_point');
    }

    /**
     * JointBox has many FiberCableSegments (as end point)
     */
    public function incomingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'end_point');
    }

    // ==================== HELPER METHODS ====================

    public function getAvailableCapacity()
    {
        return $this->capacity - $this->used_capacity;
    }

    public function getUsagePercentage()
    {
        if ($this->capacity == 0) {
            return 0;
        }
        return round(($this->used_capacity / $this->capacity) * 100, 1);
    }

    public function isFull()
    {
        return $this->used_capacity >= $this->capacity;
    }

    public function getUsageBadgeClass()
    {
        $percentage = $this->getUsagePercentage();

        if ($percentage >= 90) {
            return 'badge bg-danger';
        } elseif ($percentage >= 70) {
            return 'badge bg-warning';
        } else {
            return 'badge bg-success';
        }
    }
}

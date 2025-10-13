<?php

// app/Models/FiberCableSegment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FiberCableSegment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'cable_type',
        'core_count',
        'cable_brand',
        'cable_model',
        'start_point_type',
        'start_point_id',
        'start_latitude',
        'start_longitude',
        'start_connector_type',  // ✅ NEW
        'start_port',            // ✅ NEW
        'end_point_type',
        'end_point_id',
        'end_latitude',
        'end_longitude',
        'end_connector_type',    // ✅ NEW
        'end_port',              // ✅ NEW
        'path_coordinates',
        'distance',
        'installation_type',
        'installation_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'path_coordinates' => 'array',
        'distance' => 'float',
        'core_count' => 'integer',
        'start_latitude' => 'float',
        'start_longitude' => 'float',
        'end_latitude' => 'float',
        'end_longitude' => 'float',
    ];

    // ==================== POLYMORPHIC RELATIONSHIPS ====================

    /**
     * Get the start point (polymorphic)
     * Can be: OLT, ODF, ODC, JointBox, Splitter, ODP
     */
    public function startPoint(): MorphTo
    {
        return $this->morphTo('startPoint', 'start_point_type', 'start_point_id')
            ->withDefault(function () {
                return null;
            });
    }

    /**
     * Get the end point (polymorphic)
     * Can be: ODF, ODC, JointBox, Splitter, ODP, ONT
     */
    public function endPoint(): MorphTo
    {
        return $this->morphTo('endPoint', 'end_point_type', 'end_point_id')
            ->withDefault(function () {
                return null;
            });
    }

    /**
     * FiberCableSegment has many FiberCores
     */
    public function cores()
    {
        return $this->hasMany(FiberCore::class, 'cable_segment_id');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get available cores
     */
    public function getAvailableCores()
    {
        return $this->cores()->where('status', 'available')->count();
    }

    /**
     * Get used cores
     */
    public function getUsedCores()
    {
        return $this->cores()->where('status', 'used')->count();
    }

    /**
     * Get core usage percentage
     */
    public function getCoreUsagePercentage()
    {
        if ($this->core_count == 0) {
            return 0;
        }
        return round(($this->getUsedCores() / $this->core_count) * 100, 1);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'active' => 'badge bg-success',
            'damaged' => 'badge bg-danger',
            'maintenance' => 'badge bg-warning',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get connector type badge
     */
    public function getConnectorBadge($type)
    {
        return match($type) {
            'SC' => 'badge bg-primary',
            'LC' => 'badge bg-info',
            'FC' => 'badge bg-warning',
            'ST' => 'badge bg-secondary',
            'E2000' => 'badge bg-success',
            'MPO' => 'badge bg-dark',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get distance in KM
     */
    public function getDistanceKm()
    {
        return $this->distance ? round($this->distance / 1000, 2) : 0;
    }
}

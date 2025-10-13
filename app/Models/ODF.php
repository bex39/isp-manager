<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ODF extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'odfs';

    protected $fillable = [
        'name',
        'code',
        'olt_id',
        'location',
        'total_ports',
        'used_ports',
        'rack_number',
        'position',
        'latitude',
        'longitude',
        'address',
        'installation_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'is_active' => 'boolean',
        'total_ports' => 'integer',
        'used_ports' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * ODF belongs to OLT
     */
    public function olt()
    {
        return $this->belongsTo(OLT::class);
    }

    /**
     * ODF has many ODCs
     */
    public function odcs()
    {
        return $this->hasMany(ODC::class, 'odf_id');
    }

    /**
     * ODF has many FiberCableSegments (as start point)
     */
    public function outgoingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'start_point');
    }

    /**
     * ODF has many FiberCableSegments (as end point)
     */
    public function incomingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'end_point');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get available ports
     */
    public function getAvailablePorts()
    {
        return $this->total_ports - $this->used_ports;
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentage()
    {
        if ($this->total_ports == 0) {
            return 0;
        }
        return round(($this->used_ports / $this->total_ports) * 100, 1);
    }

    /**
     * Check if ODF is full
     */
    public function isFull()
    {
        return $this->used_ports >= $this->total_ports;
    }

    /**
     * Get usage badge class
     */
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

    /**
     * Increment used ports
     */
    public function incrementUsedPorts()
    {
        if ($this->used_ports < $this->total_ports) {
            $this->increment('used_ports');
        }
    }

    /**
     * Decrement used ports
     */
    public function decrementUsedPorts()
    {
        if ($this->used_ports > 0) {
            $this->decrement('used_ports');
        }
    }

    /**
     * Get next available port
     */
    public function getNextAvailablePort()
    {
        if ($this->isFull()) {
            return null;
        }

        // Get occupied ports from outgoing cables
        $occupiedPorts = $this->outgoingCables()
            ->whereNotNull('start_port')
            ->pluck('start_port')
            ->toArray();

        // Find first available port
        for ($i = 1; $i <= $this->total_ports; $i++) {
            if (!in_array("Port-{$i}", $occupiedPorts)) {
                return "Port-{$i}";
            }
        }

        return null;
    }
}

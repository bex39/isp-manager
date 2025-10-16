<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ODP extends Model
{
    use HasFactory;

    protected $table = 'odps';

    protected $fillable = [
        'name',
        'code',
        'type',
        'total_ports',
        'used_ports',
        'latitude',
        'longitude',
        'address',
        'installation_date',
        'notes',
        'is_active',
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
    public function olt()
    {
        return $this->belongsTo(OLT::class, 'olt_id');
    }

    /**
     * Get splitters connected to this ODP
     */
    public function splitters()
    {
        // ✅ FIX: If splitters table has odp_id column
        return $this->hasMany(Splitter::class, 'odp_id');

        // OR if using polymorphic relation:
        // return $this->morphMany(Splitter::class, 'location');
    }


    /**
     * ODP has many ONTs (TAMBAH INI)
     */
    public function onts()
    {
         return $this->hasMany(ONT::class, 'odp_id');
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
     * Check if ODP is full
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
        $this->increment('used_ports');
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

        // Get occupied ports
        $occupiedPorts = $this->onts()->pluck('odp_port')->toArray();

        // Find first available port
        for ($i = 1; $i <= $this->total_ports; $i++) {
            if (!in_array($i, $occupiedPorts)) {
                return $i;
            }
        }

        return null;
    }

    /**
 * ODP has many ODPPorts
 */
public function ports()
{
    return $this->hasMany(ODPPort::class, 'odp_id');
}

/**
 * ODP has many FiberCableSegments (as start point)
 */
public function outgoingCables()
{
    return $this->morphMany(FiberCableSegment::class, 'start_point');
}

/**
 * ODP has many FiberCableSegments (as end point)
 */
public function incomingCables()
{
    return $this->morphMany(FiberCableSegment::class, 'end_point');
}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ODC extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'odcs';

    protected $fillable = [
        'name',
        'code',
        'odf_id',
        'type',
        'total_ports',
        'used_ports',
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
     * ODC belongs to ODF
     */
    public function odf()
    {
        return $this->belongsTo(ODF::class);
    }

    /**
     * ODC has many Splitters
     */
    public function splitters()
    {
        return $this->hasMany(Splitter::class, 'odc_id');
    }

    /**
     * ODC has many FiberCableSegments (as start point)
     */
    public function outgoingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'start_point');
    }

    /**
     * ODC has many FiberCableSegments (as end point)
     */
    public function incomingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'end_point');
    }

    // ==================== HELPER METHODS ====================

    public function getAvailablePorts()
    {
        return $this->total_ports - $this->used_ports;
    }

    public function getUsagePercentage()
    {
        if ($this->total_ports == 0) {
            return 0;
        }
        return round(($this->used_ports / $this->total_ports) * 100, 1);
    }

    public function isFull()
    {
        return $this->used_ports >= $this->total_ports;
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

    public function incrementUsedPorts()
    {
        if ($this->used_ports < $this->total_ports) {
            $this->increment('used_ports');
        }
    }

    public function decrementUsedPorts()
    {
        if ($this->used_ports > 0) {
            $this->decrement('used_ports');
        }
    }

    public function getNextAvailablePort()
    {
        if ($this->isFull()) {
            return null;
        }

        $occupiedPorts = $this->outgoingCables()
            ->whereNotNull('start_port')
            ->pluck('start_port')
            ->toArray();

        for ($i = 1; $i <= $this->total_ports; $i++) {
            if (!in_array("Port-{$i}", $occupiedPorts)) {
                return "Port-{$i}";
            }
        }

        return null;
    }
}

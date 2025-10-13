<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Splitter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'odp_id',
        'odc_id',        // ✅ NEW
        'odc_port',      // ✅ NEW
        'type',
        'latitude',
        'longitude',
        'total_ports',
        'used_ports',
        'notes',
        'ratio',
        'input_ports',
        'output_ports',
        'used_outputs',
    ];

    protected $casts = [
        'total_ports' => 'integer',
        'used_ports' => 'integer',
        'input_ports' => 'integer',
        'output_ports' => 'integer',
        'used_outputs' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Splitter belongs to ODP
     */
    public function odp()
    {
        return $this->belongsTo(ODP::class, 'odp_id');
    }

    /**
     * Splitter belongs to ODC
     */
    public function odc()
    {
        return $this->belongsTo(ODC::class, 'odc_id');
    }

    /**
     * Splitter has many FiberCableSegments (as start point)
     */
    public function outgoingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'start_point');
    }

    /**
     * Splitter has many FiberCableSegments (as end point)
     */
    public function incomingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'end_point');
    }

    // ==================== HELPER METHODS ====================

    public function getAvailableOutputs()
    {
        return $this->output_ports - $this->used_outputs;
    }

    public function getUsagePercentage()
    {
        if ($this->output_ports == 0) {
            return 0;
        }
        return round(($this->used_outputs / $this->output_ports) * 100, 1);
    }

    public function isFull()
    {
        return $this->used_outputs >= $this->output_ports;
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

    public function incrementUsedOutputs()
    {
        if ($this->used_outputs < $this->output_ports) {
            $this->increment('used_outputs');
        }
    }

    public function decrementUsedOutputs()
    {
        if ($this->used_outputs > 0) {
            $this->decrement('used_outputs');
        }
    }
}

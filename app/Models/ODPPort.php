<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ODPPort extends Model
{
    use HasFactory;

    protected $table = 'odp_ports';

    protected $fillable = [
        'odp_id',
        'port_number',
        'status',
        'fiber_core_id',
        'splitter_id',
        'splitter_port',
        'ont_id',
        'notes',
    ];

    protected $casts = [
        'port_number' => 'integer',
        'splitter_port' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * ODPPort belongs to ODP
     */
    public function odp()
    {
        return $this->belongsTo(ODP::class, 'odp_id');
    }

    /**
     * ODPPort belongs to FiberCore
     */
    public function fiberCore()
    {
        return $this->belongsTo(FiberCore::class);
    }

    /**
     * ODPPort belongs to Splitter
     */
    public function splitter()
    {
        return $this->belongsTo(Splitter::class);
    }

    /**
     * ODPPort belongs to ONT
     */
    public function ont()
    {
        return $this->belongsTo(ONT::class, 'ont_id');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if port is available
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Check if port is in use
     */
    public function isUsed()
    {
        return $this->status === 'used';
    }

    /**
     * Check if port is reserved
     */
    public function isReserved()
    {
        return $this->status === 'reserved';
    }

    /**
     * Check if port is damaged
     */
    public function isDamaged()
    {
        return $this->status === 'damaged';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'available' => 'badge bg-success',
            'used' => 'badge bg-primary',
            'reserved' => 'badge bg-warning',
            'damaged' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get port label
     */
    public function getPortLabel()
    {
        return "Port-{$this->port_number}";
    }

    /**
     * Get connection info
     */
    public function getConnectionInfo()
    {
        if ($this->ont) {
            return "ONT: {$this->ont->name}";
        } elseif ($this->splitter) {
            return "Splitter: {$this->splitter->name} (Port {$this->splitter_port})";
        } elseif ($this->fiberCore) {
            return "Core: {$this->fiberCore->core_number}";
        }

        return 'Not connected';
    }
}

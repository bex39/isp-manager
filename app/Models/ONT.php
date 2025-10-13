<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ONT extends Model
{
    use SoftDeletes;

    protected $table = 'onts';

    protected $fillable = [
        'olt_id', 'customer_id', 'odp_id','odp_port','name', 'sn', 'management_ip',
        'username', 'password', 'model', 'pon_type', 'pon_port',
        'ont_id', 'wifi_ssid', 'wifi_password', 'latitude', 'longitude',
        'status', 'rx_power', 'tx_power', 'last_seen', 'is_active', 'notes'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function olt()
    {
        return $this->belongsTo(OLT::class);
    }

    /**
     * ONT belongs to ODP (TAMBAH INI)
     */
    public function odp()
    {
        return $this->belongsTo(ODP::class, 'odp_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isOnline()
    {
        return $this->status === 'online' &&
               $this->last_seen &&
               $this->last_seen->diffInMinutes(now()) < 10;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'online' => 'badge bg-success',
            'offline' => 'badge bg-secondary',
            'down' => 'badge bg-danger',
            default => 'badge bg-warning',
        };
    }

    public function getSignalQuality()
    {
        if (!$this->rx_power) return 'unknown';

        if ($this->rx_power >= -20) return 'excellent';
        if ($this->rx_power >= -25) return 'good';
        if ($this->rx_power >= -28) return 'fair';
        return 'poor';
    }

    // Add to existing ONT model

/**
 * ONT has one ODPPort
 */
public function odpPort()
{
    return $this->hasOne(ODPPort::class, 'ont_id');
}

/**
 * ONT has many FiberCableSegments (as end point)
 */
public function incomingCables()
{
    return $this->morphMany(FiberCableSegment::class, 'end_point');
}
}

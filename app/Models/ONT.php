<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ONT extends Model
{
    protected $table = 'onts';

    protected $fillable = [
        'olt_id',
        'customer_id',
        'odp_id',
        'odp_port',
        'name',
        'sn',
        'management_ip',
        'username',
        'password',
        'model',
        'pon_type',
        'pon_port',
        'ont_id',
        'wifi_ssid',
        'wifi_password',
        'status',
        'rx_power',
        'tx_power',
        'last_seen',
        'latitude',
        'longitude',
        //'address',
        //'installation_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
        'installation_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relations
    public function olt()
    {
        return $this->belongsTo(OLT::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function odp()
    {
        return $this->belongsTo(ODP::class);
    }

    // Helper Methods
    public function getSignalBadgeClass()
    {
        if (!$this->rx_power) return 'badge bg-secondary';

        $power = (float) $this->rx_power;

        if ($power >= -20) return 'badge bg-success';
        if ($power >= -25) return 'badge bg-warning';
        return 'badge bg-danger';
    }

    public function isOnline()
    {
        return $this->status === 'online';
    }
}

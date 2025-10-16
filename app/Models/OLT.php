<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OLT extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'olts';

    protected $fillable = [
        'name',
        'code',
        'brand',
        'model',
        'olt_type',
        'ip_address',
        'telnet_port',
        'ssh_port',
        'username',
        'password',
        'total_ports',
        'address',
        'latitude',
        'longitude',
        'last_seen',
        'notes',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
    ];

    // TAMBAHKAN METHOD INI
    public function getForeignKey()
    {
        return 'olt_id'; // Override default foreign key
    }

    // Relationships
    public function customers()
    {
        return $this->hasMany(Customer::class, 'olt_id'); // Specify foreign key explicitly
    }

    // Helper methods tetap sama
    public function isOnline()
    {
        // If status column exists, use it
        if (isset($this->status)) {
            return $this->status === 'online';
        }

        // Fallback: check last_seen (online if seen in last 10 minutes)
        if ($this->last_seen) {
            return $this->last_seen->gt(now()->subMinutes(10));
        }

        return false;
    }

    public function getStatusBadgeClass()
    {
        if (!$this->is_active) {
            return 'badge bg-secondary';
        }

        if (isset($this->status)) {
            return match($this->status) {
                'online' => 'badge bg-success',
                'offline' => 'badge bg-danger',
                'unreachable' => 'badge bg-warning',
                'maintenance' => 'badge bg-info',
                default => 'badge bg-secondary',
            };
        }

        // Fallback
        return $this->isOnline() ? 'badge bg-success' : 'badge bg-danger';
    }


    public function getOltTypeLabel()
    {
        $types = [
            'huawei' => 'Huawei',
            'zte' => 'ZTE',
            'fiberhome' => 'FiberHome',
            'bdcom' => 'BDCOM',
            'other' => 'Other',
        ];

        return $types[$this->olt_type] ?? $this->olt_type;
    }

    /**
 * OLT has many ODFs
 */
public function odfs()
{
    return $this->hasMany(ODF::class);
}

/**
 * OLT has many FiberCableSegments (as start point)
 */
public function outgoingCables()
{
    return $this->morphMany(FiberCableSegment::class, 'start_point');
}

/**
 * OLT has many FiberCableSegments (as end point)
 */
    public function incomingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'end_point');
    }

     public function getCustomersCountAttribute()
    {
        return $this->onts()->whereNotNull('customer_id')->count();
    }
}

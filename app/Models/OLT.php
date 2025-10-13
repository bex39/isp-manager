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
        'ip_address',
        'telnet_port',
        'ssh_port',
        'username',
        'password',
        'olt_type',
        'model',
        'address',
        'latitude',
        'longitude',
        'total_ports',
        'is_active',
        'last_seen',
        'notes',
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
        return $this->last_seen && $this->last_seen->diffInMinutes(now()) < 10;
    }

    public function getStatusBadgeClass()
    {
        if (!$this->is_active) {
            return 'badge bg-secondary';
        }

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
}

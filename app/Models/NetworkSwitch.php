<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkSwitch extends Model
{
    use SoftDeletes;

    protected $table = 'switches'; // PENTING: tetap gunakan table switches

    protected $fillable = [
        'name', 'ip_address', 'mac_address', 'brand', 'model',
        'username', 'password', 'ssh_port', 'port_count',
        'latitude', 'longitude', 'location', 'status',
        'ping_latency', 'last_seen', 'is_active', 'notes'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isOnline()
    {
        return $this->status === 'online' &&
               $this->last_seen &&
               $this->last_seen->diffInMinutes(now()) < 10;
    }

    public function getStatusBadgeClass()
    {
        return $this->status === 'online' ? 'badge bg-success' : 'badge bg-secondary';
    }
}

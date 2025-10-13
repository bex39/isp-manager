<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'ip_address',
        'mac_address',
        'ssid',
        'wifi_password',
        'frequency',
        'max_clients',
        'connected_clients',
        'username',
        'password',
        'ssh_port',
        'status',
        'is_active',
        'latitude',
        'longitude',
        'address',
        'location',
        'notes',
        'ping_latency',
        'last_seen',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_clients' => 'integer',
        'connected_clients' => 'integer',
        'ssh_port' => 'integer',
        'ping_latency' => 'float',
        'last_seen' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $attributes = [
        'is_active' => true,
        'status' => 'offline',
        'connected_clients' => 0,
        'ssh_port' => 22,
    ];
}

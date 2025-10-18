<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SwitchModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'switches';

    protected $fillable = [
    'name',
    'ip_address',
    'mac_address',
    'brand',
    'model',
    'username',
    'password',
    'ssh_port',
    'port_count',
    'latitude',
    'longitude',
    'location',
    'status',
    'ping_latency',
    'last_seen',
    'is_active',
    'notes',
];

    protected $casts = [
    'is_active' => 'boolean',
    'last_seen' => 'datetime',
    'ssh_port' => 'integer',
    'port_count' => 'integer',
    'ping_latency' => 'integer',
];

    /**
     * Check if switch is managed (has IP and credentials)
     */
    public function isManaged()
    {
        return !empty($this->ip_address)
            && !empty($this->username)
            && !empty($this->password);
    }

    /**
     * Check if switch is online
     */
    public function isOnline()
    {
        if (isset($this->status)) {
            return $this->status === 'online';
        }

        if ($this->last_seen) {
            return $this->last_seen->gt(now()->subMinutes(10));
        }

        return false;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        if (!$this->is_active) {
            return 'badge bg-secondary';
        }

        return match($this->status ?? 'unknown') {
            'online' => 'badge bg-success',
            'offline' => 'badge bg-danger',
            'unreachable' => 'badge bg-warning',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get brand display name
     */
    public function getBrandDisplayName()
    {
        return match(strtolower($this->brand ?? '')) {
            'cisco' => 'Cisco',
            'mikrotik' => 'MikroTik',
            'ubiquiti' => 'Ubiquiti',
            'tp-link' => 'TP-Link',
            'hp' => 'HP/Aruba',
            'huawei' => 'Huawei',
            'd-link' => 'D-Link',
            default => ucfirst($this->brand ?? 'Unknown'),
        };
    }

    /**
     * Get latency color class
     */
    public function getLatencyColorClass()
    {
        if (!$this->ping_latency) {
            return 'text-muted';
        }

        if ($this->ping_latency < 50) {
            return 'text-success';
        }
        if ($this->ping_latency < 100) {
            return 'text-warning';
        }
        return 'text-danger';
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    public function scopeManaged($query)
    {
        return $query->whereNotNull('ip_address')
            ->whereNotNull('username')
            ->whereNotNull('password');
    }

    public function scopeUnmanaged($query)
    {
        return $query->whereNull('ip_address')
            ->orWhereNull('username')
            ->orWhereNull('password');
    }
}

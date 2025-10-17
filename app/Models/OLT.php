<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'telnet_port' => 'integer',
        'ssh_port' => 'integer',
        'total_ports' => 'integer',
    ];

    // ==================== ENCRYPTED ATTRIBUTES ====================

    /**
     * Encrypt/Decrypt username
     */
    protected function username(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    /**
     * Encrypt/Decrypt password
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    // ==================== OVERRIDE METHODS ====================

    /**
     * Override default foreign key
     */
    public function getForeignKey()
    {
        return 'olt_id';
    }

    // ==================== RELATIONS ====================

    public function customers()
    {
        return $this->hasMany(Customer::class, 'olt_id');
    }

    public function onts()
    {
        return $this->hasMany(ONT::class);
    }

    public function odfs()
    {
        return $this->hasMany(ODF::class);
    }

    public function outgoingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'start_point');
    }

    public function incomingCables()
    {
        return $this->morphMany(FiberCableSegment::class, 'end_point');
    }

    public function provisioningQueue()
    {
        return $this->hasMany(AcsProvisioningQueue::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if OLT is online (SINGLE DEFINITION)
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
     * Get status badge class (SINGLE DEFINITION)
     */
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

        return $this->isOnline() ? 'badge bg-success' : 'badge bg-danger';
    }

    /**
     * Get OLT type label (SINGLE DEFINITION)
     */
    public function getOltTypeLabel()
    {
        $types = [
            'huawei' => 'Huawei',
            'zte' => 'ZTE',
            'fiberhome' => 'FiberHome',
            'bdcom' => 'BDCOM',
            'other' => 'Other',
        ];

        return $types[$this->olt_type] ?? ($this->olt_type ?? 'GPON');
    }

    /**
     * Get brand display name
     */
    public function getBrandDisplayName()
    {
        return match(strtolower($this->brand ?? '')) {
            'huawei' => 'Huawei',
            'zte' => 'ZTE',
            'fiberhome' => 'FiberHome',
            'bdcom' => 'BDCOM',
            default => ucfirst($this->brand ?? 'Unknown'),
        };
    }

    /**
     * Get customers count
     */
    public function getCustomersCountAttribute()
    {
        return $this->onts()->whereNotNull('customer_id')->count();
    }

    /**
     * Get ONTs count
     */
    public function getOntsCountAttribute()
    {
        return $this->onts()->count();
    }

    /**
     * Get online ONTs count
     */
    public function getOnlineOntsCount()
    {
        return $this->onts()->where('status', 'online')->count();
    }

    /**
     * Get offline ONTs count
     */
    public function getOfflineOntsCount()
    {
        return $this->onts()->where('status', 'offline')->count();
    }

    /**
     * Get port utilization percentage
     */
    public function getPortUtilization()
    {
        if (!$this->total_ports) {
            return 0;
        }

        $usedPorts = $this->onts()->distinct('pon_port')->count('pon_port');
        return round(($usedPorts / $this->total_ports) * 100, 2);
    }

    /**
     * Get available ports
     */
    public function getAvailablePorts()
    {
        if (!$this->total_ports) {
            return 0;
        }

        $usedPorts = $this->onts()->distinct('pon_port')->count('pon_port');
        return $this->total_ports - $usedPorts;
    }

    /**
     * Check if OLT is Huawei brand
     */
    public function isHuawei()
    {
        return str_contains(strtolower($this->brand ?? ''), 'huawei');
    }

    /**
     * Check if OLT is ZTE brand
     */
    public function isZTE()
    {
        return str_contains(strtolower($this->brand ?? ''), 'zte');
    }

    /**
     * Check if OLT is FiberHome brand
     */
    public function isFiberHome()
    {
        return str_contains(strtolower($this->brand ?? ''), 'fiberhome');
    }

    /**
     * Check if OLT is BDCOM brand
     */
    public function isBDCOM()
    {
        return str_contains(strtolower($this->brand ?? ''), 'bdcom');
    }

    /**
     * Get SSH connection details (decrypted)
     */
    public function getSshDetails()
    {
        return [
            'host' => $this->ip_address,
            'port' => $this->ssh_port ?? 22,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    /**
     * Get Telnet connection details (decrypted)
     */
    public function getTelnetDetails()
    {
        return [
            'host' => $this->ip_address,
            'port' => $this->telnet_port ?? 23,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    /**
     * Test if credentials are valid
     */
    public function hasValidCredentials()
    {
        return !empty($this->ip_address)
            && !empty($this->username)
            && !empty($this->password);
    }

    /**
     * Get pending provisioning jobs
     */
    public function getPendingProvisioningJobs()
    {
        return $this->provisioningQueue()
            ->where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get unprovisioned ONTs count
     */
    public function getUnprovisionedOntsCount()
    {
        return $this->onts()
            ->where('status', 'offline')
            ->whereNull('last_provision_at')
            ->count();
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

    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', 'like', "%{$brand}%");
    }

    public function scopeHuawei($query)
    {
        return $query->where('brand', 'like', '%huawei%');
    }

    public function scopeZTE($query)
    {
        return $query->where('brand', 'like', '%zte%');
    }

    public function scopeFiberHome($query)
    {
        return $query->where('brand', 'like', '%fiberhome%');
    }
}

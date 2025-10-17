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
        'is_active',
        'notes',
        'last_provision_at',
        'auto_provision_enabled',
        'provision_template_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
        'installation_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'last_provision_at' => 'datetime',
        'auto_provision_enabled' => 'boolean',
    ];

    // ==================== ✅ ADD THIS METHOD ====================

    /**
     * Override foreign key naming convention
     * Fix: Laravel generates "o_n_t_id" from "ONT" class name
     * We need "ont_id" to match database column
     */
    public function getForeignKey()
    {
        return 'ont_id';
    }

    // ==================== RELATIONS (Keep Existing) ====================

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

    // ==================== ACS RELATIONS ====================

    /**
     * ✅ UPDATED: Explicitly specify foreign key
     */
    public function session()
    {
        return $this->hasOne(AcsDeviceSession::class, 'ont_id', 'id');
    }

    /**
     * ✅ UPDATED: Explicitly specify foreign key
     */
    public function configHistories()
    {
        return $this->hasMany(AcsConfigHistory::class, 'ont_id', 'id');
    }

    /**
     * ✅ UPDATED: Explicitly specify foreign key
     */
    public function provisioningQueue()
    {
        return $this->hasMany(AcsProvisioningQueue::class, 'ont_id', 'id');
    }

    /**
     * ✅ UPDATED: Explicitly specify foreign key
     */
    public function alerts()
    {
        return $this->hasMany(AcsAlert::class, 'ont_id', 'id');
    }

    /**
     * ✅ UPDATED: Explicitly specify foreign key
     */
    public function bulkOperationDetails()
    {
        return $this->hasMany(AcsBulkOperationDetail::class, 'ont_id', 'id');
    }

    /**
     * Provision Template (Keep as is)
     */
    public function provisionTemplate()
    {
        return $this->belongsTo(AcsConfigTemplate::class, 'provision_template_id');
    }

    // ==================== HELPER METHODS (Keep All Existing) ====================

    public function isOnline()
    {
        return $this->status === 'online';
    }

    public function getSignalBadgeClass()
    {
        if (!$this->rx_power) {
            return 'badge bg-secondary';
        }

        $power = (float) $this->rx_power;

        if ($power >= -20) {
            return 'badge bg-success';
        }
        if ($power >= -25) {
            return 'badge bg-warning';
        }

        return 'badge bg-danger';
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'online' => 'badge bg-success',
            'offline' => 'badge bg-danger',
            'los' => 'badge bg-warning',
            'disabled' => 'badge bg-secondary',
            default => 'badge bg-secondary',
        };
    }

    // ==================== ACS HELPER METHODS (Keep All) ====================

    public function isAcsManaged()
    {
        return $this->session !== null;
    }

    public function getAcsStatus()
    {
        if (!$this->session) {
            return 'unmanaged';
        }

        return $this->session->isOnline() ? 'online' : 'offline';
    }

    public function getLastProvisionInfo()
    {
        return $this->configHistories()
            ->where('action', 'provision')
            ->where('status', 'success')
            ->latest()
            ->first();
    }

    public function getLastConfigChange()
    {
        return $this->configHistories()
            ->latest()
            ->first();
    }

    public function canAutoProvision()
    {
        return $this->auto_provision_enabled && $this->is_active;
    }

    public function getPendingConfigs()
    {
        return $this->configHistories()
            ->where('status', 'pending')
            ->get();
    }

    public function getActiveAlerts()
    {
        return $this->alerts()
            ->whereIn('status', ['new', 'acknowledged'])
            ->get();
    }

    public function hasCriticalAlerts()
    {
        return $this->alerts()
            ->where('severity', 'critical')
            ->where('status', 'new')
            ->exists();
    }

    public function getSignalQuality()
    {
        if (!$this->rx_power) {
            return 'Unknown';
        }

        $power = (float) $this->rx_power;

        if ($power >= -20) {
            return 'Excellent';
        }
        if ($power >= -23) {
            return 'Good';
        }
        if ($power >= -25) {
            return 'Fair';
        }

        return 'Poor';
    }

    public function getDaysSinceLastProvision()
    {
        if (!$this->last_provision_at) {
            return null;
        }

        return $this->last_provision_at->diffInDays(now());
    }

    public function createOrUpdateSession($data = [])
    {
        return $this->session()->updateOrCreate(
            ['ont_id' => $this->id],
            array_merge([
                'session_id' => uniqid('acs_', true),
                'remote_ip' => $this->management_ip,
                'last_inform' => now(),
                'parameters' => [
                    'sn' => $this->sn,
                    'model' => $this->model,
                    'wifi_ssid' => $this->wifi_ssid,
                ],
            ], $data)
        );
    }

    public function queueForProvisioning($type = 'manual', $priority = 'normal', $configData = null)
    {
        return AcsProvisioningQueue::create([
            'ont_id' => $this->id,
            'olt_id' => $this->olt_id,
            'sn' => $this->sn,
            'pon_port' => $this->pon_port,
            'ont_id_number' => $this->ont_id,
            'provision_type' => $type,
            'config_data' => $configData ?? [
                'wifi_ssid' => $this->wifi_ssid,
                'wifi_password' => $this->wifi_password,
                'template_id' => $this->provision_template_id,
            ],
            'priority' => $priority,
            'status' => 'pending',
        ]);
    }

    public function logConfigChange($action, $parameters = [], $status = 'pending', $executedBy = null)
    {
        return $this->configHistories()->create([
            'action' => $action,
            'parameters' => $parameters,
            'status' => $status,
            'executed_by' => $executedBy ?? auth()->id(),
            'executed_at' => $status === 'success' ? now() : null,
        ]);
    }

    public function markAsProvisioned()
    {
        $this->update([
            'last_provision_at' => now(),
            'status' => 'online',
        ]);
    }

    // ==================== SCOPES (Keep All) ====================

    public function scopeAcsManaged($query)
    {
        return $query->whereHas('session');
    }

    public function scopeAutoProvisionEnabled($query)
    {
        return $query->where('auto_provision_enabled', true);
    }

    public function scopeNeedsProvisioning($query)
    {
        return $query->where('status', 'offline')
            ->where('auto_provision_enabled', true)
            ->where('is_active', true);
    }

    public function scopeWithSignalIssues($query)
    {
        return $query->where('rx_power', '<', -25);
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    public function scopeLos($query)
    {
        return $query->where('status', 'los');
    }
}

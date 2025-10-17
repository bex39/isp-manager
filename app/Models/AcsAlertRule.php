<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcsAlertRule extends Model
{
    protected $fillable = [
        'name',
        'condition_type',
        'condition_parameters',
        'notification_channels',
        'recipients',
        'check_interval',
        'cooldown_period',
        'is_active',
    ];

    protected $casts = [
        'condition_parameters' => 'array',
        'notification_channels' => 'array',
        'recipients' => 'array',
        'check_interval' => 'integer',
        'cooldown_period' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relations
    public function alerts()
    {
        return $this->hasMany(AcsAlert::class, 'rule_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('condition_type', $type);
    }

    // Helper Methods
    public function getConditionValue($key, $default = null)
    {
        return data_get($this->condition_parameters, $key, $default);
    }

    public function shouldTrigger(ONT $ont)
    {
        // Check cooldown
        $lastAlert = $this->alerts()
            ->where('ont_id', $ont->id)
            ->where('triggered_at', '>=', now()->subSeconds($this->cooldown_period))
            ->latest('triggered_at')
            ->first();

        if ($lastAlert) {
            return false; // Still in cooldown
        }

        // Check condition based on type
        return match($this->condition_type) {
            'offline' => $ont->status === 'offline',
            'signal_low' => $ont->rx_power < $this->getConditionValue('threshold', -25),
            'los' => $ont->status === 'los',
            default => false,
        };
    }
}

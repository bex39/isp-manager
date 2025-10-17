<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcsAlert extends Model
{
    protected $fillable = [
        'rule_id',
        'ont_id',
        'alert_type',
        'severity',
        'message',
        'details',
        'triggered_at',
        'resolved_at',
        'acknowledged_at',
        'acknowledged_by',
        'status',
    ];

    protected $casts = [
        'details' => 'array',
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    // Relations
    public function rule()
    {
        return $this->belongsTo(AcsAlertRule::class, 'rule_id');
    }

    public function ont()
    {
        return $this->belongsTo(ONT::class);
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('status', 'acknowledged');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('triggered_at', '>=', now()->subHours($hours));
    }

    // Helper Methods
    public function acknowledge(User $user)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $user->id,
        ]);
    }

    public function resolve()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function autoResolve()
    {
        $this->update([
            'status' => 'auto_resolved',
            'resolved_at' => now(),
        ]);
    }

    public function getDuration()
    {
        if (!$this->resolved_at) {
            return $this->triggered_at->diffForHumans(null, true);
        }

        return $this->triggered_at->diffForHumans($this->resolved_at, true);
    }
}

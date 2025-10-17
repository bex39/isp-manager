<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcsConfigTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'parameters',
        'is_default',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function histories()
    {
        return $this->hasMany(AcsConfigHistory::class, 'template_id');
    }

    public function onts()
    {
        return $this->hasMany(ONT::class, 'provision_template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Helper Methods
    public function getParameterValue($key, $default = null)
    {
        return data_get($this->parameters, $key, $default);
    }

    public function setParameterValue($key, $value)
    {
        $parameters = $this->parameters ?? [];
        data_set($parameters, $key, $value);
        $this->parameters = $parameters;
    }

    public function getUsageCount()
    {
        return $this->onts()->count();
    }
}

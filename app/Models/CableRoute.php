<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CableRoute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'from_device_id',
        'from_device_type',
        'to_device_id',
        'to_device_type',
        'path_coordinates',
        'distance_meters',
        'cable_type',
        'color',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'path_coordinates' => 'array',
        'is_active' => 'boolean',
    ];

    public function getTypeLabel()
    {
        return match($this->type) {
            'backbone' => 'Backbone',
            'distribution' => 'Distribution',
            'drop' => 'Drop Cable',
            default => $this->type
        };
    }
}

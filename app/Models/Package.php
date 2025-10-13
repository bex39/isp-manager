<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'download_speed',
        'upload_speed',
        'price',
        'has_fup',
        'fup_quota',
        'fup_speed',
        'billing_cycle',
        'grace_period',
        'burst_limit',
        'priority',
        'connection_limit',
        'available_for',
        'is_active',
    ];

    protected $casts = [
        'has_fup' => 'boolean',
        'is_active' => 'boolean',
        'available_for' => 'array',
        'price' => 'decimal:2',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function getFormattedPrice()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getSpeedLabel()
    {
        return $this->download_speed . '/' . $this->upload_speed . ' Mbps';
    }
}

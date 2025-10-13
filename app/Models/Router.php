<?php

// app/Models/Router.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Router extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'ssh_port',
        'api_port',
        'username',
        'password',
        'ros_version',
        'address',
        'latitude',
        'longitude',
        'coverage_radius',
        'is_active',
        'last_seen',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function isOnline()
    {
        // Router dianggap online jika last_seen kurang dari 5 menit
        return $this->last_seen && $this->last_seen->diffInMinutes(now()) < 5;
    }


}

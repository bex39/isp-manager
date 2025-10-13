<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'id_card_number',
        'latitude',
        'longitude',
        'connection_type',
        'connection_config',
        'package_id',
        'custom_speed_download',
        'custom_speed_upload',
        'installation_date',
        'next_billing_date',
        'router_id',
        'olt_id',
        'ont_serial_number',
        'pon_port',
        'customer_mikrotik_ip',
        'customer_mikrotik_username',
        'customer_mikrotik_password',
        'customer_mikrotik_version',
        'status',
        'notes',
        'assigned_teknisi_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'connection_config' => 'array',
        'installation_date' => 'date',
        'next_billing_date' => 'date',
    ];

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function olt()
    {
        return $this->belongsTo(OLT::class);
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'assigned_teknisi_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public function unpaidInvoices()
    {
        return $this->hasMany(Invoice::class)->where('status', 'unpaid');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function getConnectionTypeLabel()
    {
        $types = [
            'pppoe_direct' => 'PPPoE Direct',
            'pppoe_mikrotik' => 'PPPoE via Customer MikroTik',
            'static_ip' => 'Static IP',
            'hotspot' => 'Hotspot',
            'dhcp' => 'DHCP',
        ];

        return $types[$this->connection_type] ?? $this->connection_type;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'active' => 'badge-active',
            'suspended' => 'badge badge-warning',
            'terminated' => 'badge-inactive',
            default => 'badge badge-secondary',
        };
    }

    // Generate customer code
    public static function generateCustomerCode()
    {
        $lastCustomer = self::latest('id')->first();
        $number = $lastCustomer ? $lastCustomer->id + 1 : 1;
        return 'CUST-' . date('Ym') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Authentication methods
    public function getAuthIdentifier()
    {
        return $this->id; // ← TAMBAHKAN METHOD INI - Return ID (integer)
    }

    public function getAuthIdentifierName()
    {
        return 'email'; // Login menggunakan kolom email
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
}

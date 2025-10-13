<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RadiusAccounting extends Model
{
    use HasFactory;

    protected $table = 'radacct';
    protected $primaryKey = 'radacctid';
    public $timestamps = false;

    protected $fillable = [
        'acctsessionid',
        'acctuniqueid',
        'username',
        'realm',
        'nasipaddress',
        'nasportid',
        'nasporttype',
        'acctstarttime',
        'acctupdatetime',
        'acctstoptime',
        'acctsessiontime',
        'acctauthentic',
        'connectinfo_start',
        'connectinfo_stop',
        'acctinputoctets',
        'acctoutputoctets',
        'calledstationid',
        'callingstationid',
        'acctterminatecause',
        'servicetype',
        'framedprotocol',
        'framedipaddress',
    ];

    protected $casts = [
        'acctstarttime' => 'datetime',
        'acctupdatetime' => 'datetime',
        'acctstoptime' => 'datetime',
    ];

    // Get customer by username
    public function customer()
    {
        return Customer::whereJsonContains('connection_config->username', $this->username)->first();
    }

    // Format bytes to human readable
    public static function formatBytes($bytes, $precision = 2)
    {
        if ($bytes == 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes) / log(1024));

        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    // Get total usage
    public function getTotalUsage()
    {
        return ($this->acctinputoctets ?? 0) + ($this->acctoutputoctets ?? 0);
    }

    // Get download
    public function getDownload()
    {
        return $this->acctinputoctets ?? 0;
    }

    // Get upload
    public function getUpload()
    {
        return $this->acctoutputoctets ?? 0;
    }

    // Scopes
    public function scopeForUsername($query, $username)
    {
        return $query->where('username', $username);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('acctstarttime', now()->month)
                    ->whereYear('acctstarttime', now()->year);
    }

    public function scopeActiveSessions($query)
    {
        return $query->whereNull('acctstoptime');
    }
}

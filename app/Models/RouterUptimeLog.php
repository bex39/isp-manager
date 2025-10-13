<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RouterUptimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'is_online',
        'error_message',
        'checked_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}

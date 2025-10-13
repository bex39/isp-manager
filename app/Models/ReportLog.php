<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLog extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'format',
        'period_start',
        'period_end',
        'filters',
        'file_path',
    ];

    protected $casts = [
        'filters' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

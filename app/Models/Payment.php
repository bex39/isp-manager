<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_method',
        'payment_date',
        'amount',
        'notes',
        'tripay_reference',
        'tripay_merchant_ref',
        'checkout_url',
        'qr_url',
        'expired_at',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship to Invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'package_id',
        'invoice_number',
        'invoice_counter',
        'issue_date',
        'due_date',
        'period',
        'description',
        'items',
        'subtotal',
        'tax_percentage',
        'tax',
        'discount',
        'late_fee',
        'total',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    // ==================== QUERY SCOPES ====================

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'unpaid')
                     ->where('due_date', '<', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // ==================== ACCESSORS ====================

    public function getTaxAmountAttribute()
    {
        return $this->tax;
    }

    public function getDiscountAmountAttribute()
    {
        return $this->discount;
    }

    public function getTotalAmountAttribute()
    {
        return $this->total;
    }

    // ==================== HELPER METHODS ====================

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isUnpaid()
    {
        return $this->status === 'unpaid';
    }

    public function isOverdue()
    {
        return $this->status === 'unpaid' && $this->due_date->isPast();
    }

    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    public function markAsPaid($paidAt = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => $paidAt ?? now(),
        ]);
    }

    // ==================== FORMATTING METHODS ====================

    public function getFormattedTotal()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function getFormattedSubtotal()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedTax()
    {
        return 'Rp ' . number_format($this->tax, 0, ',', '.');
    }

    public function getFormattedDiscount()
    {
        return 'Rp ' . number_format($this->discount, 0, ',', '.');
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'paid' => 'badge bg-success',
            'unpaid' => 'badge bg-warning text-dark',
            'overdue' => 'badge bg-danger',
            'pending' => 'badge bg-info',
            'cancelled' => 'badge bg-secondary',
            default => 'badge bg-secondary',
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'overdue' => 'Overdue',
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    // ==================== STATIC METHODS ====================

    public static function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Ym');
        $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches);
            $lastNumber = isset($matches[1]) ? intval($matches[1]) : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    // ==================== BOOT METHOD ====================

    protected static function booted()
    {
        // Auto update status ke overdue jika lewat due date
        static::retrieved(function ($invoice) {
            if ($invoice->status === 'unpaid' && $invoice->due_date->isPast()) {
                $invoice->setAttribute('status', 'overdue');
            }
        });
    }
}

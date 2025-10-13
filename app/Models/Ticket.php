<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'priority',
        'status',
        'category',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses()
    {
        return $this->hasMany(TicketResponse::class);
    }

    // Helper Methods
    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'open' => 'badge bg-primary',
            'in_progress' => 'badge bg-info',
            'waiting_customer' => 'badge bg-warning',
            'resolved' => 'badge bg-success',
            'closed' => 'badge bg-secondary',
            default => 'badge bg-secondary',
        };
    }

    public function getPriorityBadgeClass()
    {
        return match($this->priority) {
            'low' => 'badge bg-secondary',
            'medium' => 'badge bg-info',
            'high' => 'badge bg-warning',
            'urgent' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    public function getResponseTime()
    {
        $firstResponse = $this->responses()->oldest()->first();

        if (!$firstResponse) {
            return null;
        }

        return $this->created_at->diffInMinutes($firstResponse->created_at);
    }

    public function getResolutionTime()
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->resolved_at);
    }

    // Generate Ticket Number
    public static function generateTicketNumber()
    {
        $lastTicket = self::latest('id')->first();
        $number = $lastTicket ? $lastTicket->id + 1 : 1;
        return 'TKT-' . date('Ym') . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberCore extends Model
{
    use HasFactory;

    protected $fillable = [
        'cable_segment_id',
        'core_number',
        'core_color',
        'tube_number',
        'status',
        'connected_to_type',
        'connected_to_id',
        'loss_db',
        'length_km',
        'notes',
    ];

    protected $casts = [
        'core_number' => 'integer',
        'loss_db' => 'float',
        'length_km' => 'float',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * FiberCore belongs to FiberCableSegment
     */
    public function cableSegment()
    {
        return $this->belongsTo(FiberCableSegment::class, 'cable_segment_id');
    }

    /**
     * Get the connected equipment (polymorphic)
     * Can be: Splitter, ONT, ODPPort
     */
    public function connectedTo()
    {
        return $this->morphTo('connected_to');
    }

    /**
     * FiberCore has many test results
     */
    public function testResults()
    {
        return $this->hasMany(FiberTestResult::class);
    }

    /**
     * FiberCore can be used in splices (as input)
     */
    public function inputSplices()
    {
        return $this->hasMany(FiberSplice::class, 'input_segment_id')
            ->where('input_core_number', $this->core_number);
    }

    /**
     * FiberCore can be used in splices (as output)
     */
    public function outputSplices()
    {
        return $this->hasMany(FiberSplice::class, 'output_segment_id')
            ->where('output_core_number', $this->core_number);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if core is available
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Check if core is in use
     */
    public function isUsed()
    {
        return $this->status === 'used';
    }

    /**
     * Check if core is damaged
     */
    public function isDamaged()
    {
        return $this->status === 'damaged';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'available' => 'badge bg-success',
            'used' => 'badge bg-primary',
            'reserved' => 'badge bg-warning',
            'damaged' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get core color badge
     */
    public function getColorBadge()
{
    $colors = [
        'Blue' => 'bg-primary',
        'Orange' => 'text-white',
        'Green' => 'bg-success',
        'Brown' => 'text-white',
        'Slate' => 'bg-secondary',
        'White' => 'bg-light text-dark',
        'Red' => 'bg-danger',
        'Black' => 'bg-dark',
        'Yellow' => 'bg-warning',
        'Violet' => 'text-white',
        'Rose' => 'text-dark',
        'Aqua' => 'bg-info',
    ];

    $colorClass = $colors[$this->core_color] ?? 'bg-secondary';

    // Custom styling for special colors
    $customStyles = [
        'Orange' => 'background: orange; color: white;',
        'Brown' => 'background: brown; color: white;',
        'White' => 'background: white; color: black; border: 1px solid #ddd;',
        'Violet' => 'background: violet; color: white;',
        'Rose' => 'background: pink; color: black;',
    ];

    if (isset($customStyles[$this->core_color])) {
        return [
            'class' => 'badge',
            'style' => $customStyles[$this->core_color]
        ];
    }

    return [
        'class' => 'badge ' . $colorClass,
        'style' => ''
    ];
}

/**
 * Get standard fiber core colors (TIA-598 standard)
 */
public static function getCoreColors()
{
    return [
        'Blue',
        'Orange',
        'Green',
        'Brown',
        'Slate',
        'White',
        'Red',
        'Black',
        'Yellow',
        'Violet',
        'Rose',
        'Aqua',
    ];
}

    /**
     * Get loss quality status
     */
    public function getLossQuality()
    {
        if (!$this->loss_db) {
            return 'unknown';
        }

        if ($this->loss_db <= 0.3) {
            return 'excellent';
        } elseif ($this->loss_db <= 0.5) {
            return 'good';
        } elseif ($this->loss_db <= 1.0) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get latest test result
     */
    public function getLatestTest()
    {
        return $this->testResults()->latest('test_date')->first();
    }
}

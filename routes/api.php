<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FiberCoreController;

// Fiber Core API
Route::get('/cores/{core}', [FiberCoreController::class, 'showApi']);

// Equipment API - Handle different column structures
Route::get('/equipment/{type}', function($type) {
    $model = match($type) {
        'olt' => \App\Models\OLT::class,
        'odf' => \App\Models\ODF::class,
        'odc' => \App\Models\ODC::class,
        'joint_box' => \App\Models\JointBox::class,
        'splitter' => \App\Models\Splitter::class,
        'odp' => \App\Models\ODP::class,
        'ont' => \App\Models\ONT::class,
        default => null
    };

    if (!$model) {
        return response()->json(['error' => 'Invalid equipment type'], 404);
    }

    try {
        // Get all records (no select to avoid column errors)
        $items = $model::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($item) use ($type) {
                // Get identifier based on equipment type
                $identifier = '';

                if ($type === 'olt') {
                    // OLT: use IP address or model as identifier
                    $identifier = $item->ip_address ?? $item->model ?? '';
                } elseif ($type === 'ont') {
                    // ONT: use serial_number
                    $identifier = $item->serial_number ?? '';
                } else {
                    // Others: use code
                    $identifier = $item->code ?? '';
                }

                // Build display name
                $displayName = $item->name;
                if ($identifier) {
                    $displayName .= ' (' . $identifier . ')';
                }
                if (!$item->is_active) {
                    $displayName .= ' [OFFLINE]';
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $identifier,
                    'is_active' => $item->is_active ?? true,
                    'display_name' => $displayName
                ];
            });

        return response()->json($items);

    } catch (\Exception $e) {
        \Log::error("Equipment API error for type {$type}: " . $e->getMessage());
        return response()->json([
            'error' => $e->getMessage(),
            'type' => $type
        ], 500);
    }
});

// Get available cores for cable segment
    Route::get('cable-segments/{segment}/available-cores', function($segmentId) {
        try {
            $segment = \App\Models\FiberCableSegment::find($segmentId);

            if (!$segment) {
                return response()->json([], 404);
            }

            $cores = $segment->cores()
                ->where('status', 'available')
                ->orderBy('core_number')
                ->get(['id', 'core_number', 'core_color']);

            \Log::info("Available cores for segment {$segmentId}: " . $cores->count());

            return response()->json($cores);

        } catch (\Exception $e) {
            \Log::error("Error loading cores for segment {$segmentId}: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    // Get splice data for editing
    Route::get('fiber-splices/{splice}', function($spliceId) {
        try {
            $splice = \App\Models\FiberSplice::find($spliceId);

            if (!$splice) {
                return response()->json(['error' => 'Not found'], 404);
            }

            return response()->json($splice);

        } catch (\Exception $e) {
            \Log::error("Error loading splice {$spliceId}: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('fiber-splices/{splice}', function($spliceId) {
        try {
            $splice = \App\Models\FiberSplice::find($spliceId);

            if (!$splice) {
                return response()->json(['error' => 'Splice not found'], 404);
            }

            return response()->json($splice);

        } catch (\Exception $e) {
            \Log::error("Error loading splice {$spliceId}: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });




<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\OLT;
use App\Models\ONT;
use App\Models\Customer;
use App\Models\NetworkSwitch;
use App\Models\AccessPoint;
use App\Models\JointBox;
use App\Models\FiberCableSegment;
use App\Models\ODP;
use Illuminate\Http\Request;

class MapController extends Controller
{
   public function index()
{
    // HAPUS where is_active/status - ambil SEMUA
    $routers = Router::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get(); // Ambil semua, termasuk offline

    $olts = OLT::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get(); // Ambil semua

    $onts = ONT::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();

    $switches = NetworkSwitch::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();

    $accessPoints = AccessPoint::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();

    $customers = Customer::where('status', 'active')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();

    $jointBoxes = JointBox::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();

    $cableSegments = FiberCableSegment::with(['cores'])->get();

    $odps = ODP::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();

    // Format data untuk view
    $mapData = [
        'routers' => $routers->map(function($router) {
            return [
                'id' => $router->id,
                'name' => $router->name,
                'lat' => (float) $router->latitude,
                'lng' => (float) $router->longitude,
                'ip' => $router->ip_address,
                'is_online' => $router->status === 'online', // Tetap track status
                'customers_count' => 0,
                'coverage_radius' => null,
                'url' => route('routers.show', $router),
            ];
        }),
        'olts' => $olts->map(function($olt) {
            return [
                'id' => $olt->id,
                'name' => $olt->name,
                'lat' => (float) $olt->latitude,
                'lng' => (float) $olt->longitude,
                'ip' => $olt->ip_address,
                'is_online' => $olt->status === 'online',
                'customers_count' => ONT::where('olt_id', $olt->id)->count(),
                'url' => route('olts.show', $olt),
            ];
        }),

        'odps' => $odps->map(function($odp) {
            return [
                'id' => $odp->id,
                'name' => $odp->name,
                'code' => $odp->code,
                'lat' => (float) $odp->latitude,
                'lng' => (float) $odp->longitude,
                'total_ports' => $odp->total_ports,
                'used_ports' => $odp->used_ports,
                'available_ports' => $odp->getAvailablePorts(),
                'url' => route('odps.show', $odp),
            ];
        }),
        'onts' => $onts->map(function($ont) {
            return [
                'id' => $ont->id,
                'name' => $ont->name,
                'lat' => (float) $ont->latitude,
                'lng' => (float) $ont->longitude,
                'ip' => $ont->management_ip,
                'status' => $ont->status ?? 'offline',
                'customer' => $ont->customer->name ?? 'N/A',
                'signal' => $ont->rx_power,
                'url' => route('onts.show', $ont),
            ];
        }),
        'switches' => $switches->map(function($switch) {
            return [
                'id' => $switch->id,
                'name' => $switch->name,
                'lat' => (float) $switch->latitude,
                'lng' => (float) $switch->longitude,
                'ip' => $switch->ip_address,
                'brand' => $switch->brand ?? 'N/A',
                'status' => $switch->status ?? 'offline',
                'url' => route('switches.show', $switch),
            ];
        }),
        'accessPoints' => $accessPoints->map(function($ap) {
            return [
                'id' => $ap->id,
                'name' => $ap->name,
                'lat' => (float) $ap->latitude,
                'lng' => (float) $ap->longitude,
                'ip' => $ap->ip_address,
                'ssid' => $ap->ssid,
                'status' => $ap->status ?? 'offline',
                'clients' => $ap->connected_clients ?? 0,
                'url' => route('access-points.show', $ap),
            ];
        }),
        'customers' => $customers->map(function($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'lat' => (float) $customer->latitude,
                'lng' => (float) $customer->longitude,
                'package' => $customer->package->name ?? 'N/A',
                'status' => $customer->status,
                'url' => route('customers.show', $customer),
            ];
        }),
        // TAMBAHAN FIBER INFRASTRUCTURE
        'jointBoxes' => $jointBoxes->map(function($jb) {
            return [
                'id' => $jb->id,
                'name' => $jb->name,
                'code' => $jb->code,
                'lat' => (float) $jb->latitude,
                'lng' => (float) $jb->longitude,
                'type' => $jb->type,
                'capacity' => $jb->capacity,
                'used_capacity' => $jb->used_capacity,
                'url' => route('joint-boxes.show', $jb),
            ];
        }),
        'cableSegments' => $cableSegments->map(function($cable) {
            $startLat = $cable->start_latitude;
            $startLng = $cable->start_longitude;
            $endLat = $cable->end_latitude;
            $endLng = $cable->end_longitude;

            if (!$startLat || !$startLng) {
                $startPoint = $cable->startPoint;
                if ($startPoint) {
                    $startLat = $startPoint->latitude;
                    $startLng = $startPoint->longitude;
                }
            }

            if (!$endLat || !$endLng) {
                $endPoint = $cable->endPoint;
                if ($endPoint) {
                    $endLat = $endPoint->latitude;
                    $endLng = $endPoint->longitude;
                }
            }

            return [
                'id' => $cable->id,
                'name' => $cable->name,
                'code' => $cable->code,
                'cable_type' => $cable->cable_type,
                'status' => $cable->status,
                'core_count' => $cable->core_count,
                'used_cores' => $cable->getUsedCores(),
                'start_lat' => (float) $startLat,
                'start_lng' => (float) $startLng,
                'end_lat' => (float) $endLat,
                'end_lng' => (float) $endLng,
                'url' => route('cable-segments.show', $cable),
            ];
        }),
    ];

    return view('map.index', compact('mapData'));
}

    public function fiberMap()
    {
        $routers = Router::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $olts = OLT::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $odps = ODP::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $jointBoxes = JointBox::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $cableSegments = FiberCableSegment::where('status', 'active')
            ->with(['cores'])
            ->get();

        $fiberData = [
            'routers' => $routers->map(function($router) {
                return [
                    'id' => $router->id,
                    'name' => $router->name,
                    'latitude' => (float) $router->latitude,
                    'longitude' => (float) $router->longitude,
                    'ip_address' => $router->ip_address,
                ];
            }),
            'olts' => $olts->map(function($olt) {
                return [
                    'id' => $olt->id,
                    'name' => $olt->name,
                    'latitude' => (float) $olt->latitude,
                    'longitude' => (float) $olt->longitude,
                    'ip_address' => $olt->ip_address,
                ];
            }),
            'odps' => $odps->map(function($odp) {
                return [
                    'id' => $odp->id,
                    'name' => $odp->name,
                    'code' => $odp->code,
                    'latitude' => (float) $odp->latitude,
                    'longitude' => (float) $odp->longitude,
                    'available_ports' => $odp->getAvailablePorts(),
                    'total_ports' => $odp->total_ports,
                ];
            }),
            'jointBoxes' => $jointBoxes->map(function($jb) {
                return [
                    'id' => $jb->id,
                    'name' => $jb->name,
                    'code' => $jb->code,
                    'type' => $jb->type,
                    'latitude' => (float) $jb->latitude,
                    'longitude' => (float) $jb->longitude,
                    'capacity' => $jb->capacity,
                    'used_capacity' => $jb->used_capacity,
                    'usage_percentage' => $jb->getUsagePercentage(),
                ];
            }),
            'cableSegments' => $cableSegments->map(function($cable) {
                // Get coordinates
                $startLat = $cable->start_latitude;
                $startLng = $cable->start_longitude;
                $endLat = $cable->end_latitude;
                $endLng = $cable->end_longitude;

                // If coordinates not set in cable, get from related models
                if (!$startLat || !$startLng) {
                    $startPoint = $cable->startPoint;
                    if ($startPoint) {
                        $startLat = $startPoint->latitude;
                        $startLng = $startPoint->longitude;
                    }
                }

                if (!$endLat || !$endLng) {
                    $endPoint = $cable->endPoint;
                    if ($endPoint) {
                        $endLat = $endPoint->latitude;
                        $endLng = $endPoint->longitude;
                    }
                }

                return [
                    'id' => $cable->id,
                    'name' => $cable->name,
                    'code' => $cable->code,
                    'type' => $cable->cable_type,
                    'core_count' => $cable->core_count,
                    'available_cores' => $cable->getAvailableCores(),
                    'used_cores' => $cable->getUsedCores(),
                    'usage_percentage' => $cable->getCoreUsagePercentage(),
                    'status' => $cable->status,
                    'start_point' => [
                        'lat' => (float) $startLat,
                        'lng' => (float) $startLng,
                        'type' => class_basename($cable->start_point_type),
                        'name' => $cable->startPoint->name ?? 'Unknown'
                    ],
                    'end_point' => [
                        'lat' => (float) $endLat,
                        'lng' => (float) $endLng,
                        'type' => class_basename($cable->end_point_type),
                        'name' => $cable->endPoint->name ?? 'Unknown'
                    ],
                    'coordinates' => $cable->path_coordinates ?? [
                        [$startLat, $startLng],
                        [$endLat, $endLng]
                    ],
                ];
            })
        ];

        return view('map.fiber', compact('fiberData'));
    }

    public function getNetworkData()
    {
        $routers = Router::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'latitude', 'longitude', 'status']);

        $olts = OLT::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'latitude', 'longitude', 'status']);

        $onts = ONT::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'latitude', 'longitude', 'status']);

        $jointBoxes = JointBox::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $cableSegments = FiberCableSegment::where('status', 'active')->get();

        return response()->json([
            'routers' => $routers,
            'olts' => $olts,
            'onts' => $onts,
            'jointBoxes' => $jointBoxes,
            'cableSegments' => $cableSegments,
        ]);
    }
}

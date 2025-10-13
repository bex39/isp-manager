<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use App\Models\RouterUptimeLog;
use App\Services\MikrotikService;

class CheckRouterUptime extends Command
{
    protected $signature = 'routers:check-uptime';
    protected $description = 'Check all routers uptime status';

    public function handle()
    {
        $this->info('Checking router uptime...');

        $routers = Router::where('is_active', true)->get();
        $online = 0;
        $offline = 0;

        foreach ($routers as $router) {
            try {
                $mikrotik = new MikrotikService($router);
                $isOnline = $mikrotik->testConnection();

                RouterUptimeLog::create([
                    'router_id' => $router->id,
                    'is_online' => true,
                    'checked_at' => now(),
                ]);

                $router->update(['last_seen' => now()]);
                $online++;

                $this->info("✓ {$router->name} - ONLINE");

            } catch (\Exception $e) {
                RouterUptimeLog::create([
                    'router_id' => $router->id,
                    'is_online' => false,
                    'error_message' => $e->getMessage(),
                    'checked_at' => now(),
                ]);

                $offline++;
                $this->error("✗ {$router->name} - OFFLINE");
            }
        }

        $this->info("\nSummary: {$online} online, {$offline} offline");
        return 0;
    }
}

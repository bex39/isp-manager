<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\RadiusAccounting;
use Carbon\Carbon;

class UsageService
{
    /**
     * Get usage for customer this month
     */
    public function getMonthlyUsage(Customer $customer)
    {
        if (!isset($customer->connection_config['username'])) {
            return null;
        }

        $username = $customer->connection_config['username'];

        $usage = RadiusAccounting::forUsername($username)
            ->currentMonth()
            ->selectRaw('
                SUM(acctinputoctets) as total_download,
                SUM(acctoutputoctets) as total_upload,
                SUM(acctinputoctets + acctoutputoctets) as total_usage,
                SUM(acctsessiontime) as total_time
            ')
            ->first();

        $package = $customer->package;
        $fupLimit = $package->fup_limit ?? 0; // dalam GB

        return [
            'download' => $usage->total_download ?? 0,
            'upload' => $usage->total_upload ?? 0,
            'total' => $usage->total_usage ?? 0,
            'total_time' => $usage->total_time ?? 0,
            'fup_limit' => $fupLimit * 1024 * 1024 * 1024, // Convert GB to bytes
            'fup_used_percentage' => $fupLimit > 0 ? (($usage->total_usage ?? 0) / ($fupLimit * 1024 * 1024 * 1024)) * 100 : 0,
            'formatted' => [
                'download' => RadiusAccounting::formatBytes($usage->total_download ?? 0),
                'upload' => RadiusAccounting::formatBytes($usage->total_upload ?? 0),
                'total' => RadiusAccounting::formatBytes($usage->total_usage ?? 0),
            ]
        ];
    }

    /**
     * Get daily usage for chart (last 30 days)
     */
    public function getDailyUsageChart(Customer $customer)
    {
        if (!isset($customer->connection_config['username'])) {
            return [];
        }

        $username = $customer->connection_config['username'];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $usage = RadiusAccounting::forUsername($username)
                ->whereDate('acctstarttime', $date)
                ->selectRaw('SUM(acctinputoctets + acctoutputoctets) as total')
                ->first();

            $data[] = [
                'date' => $date->format('d M'),
                'usage' => $usage->total ?? 0,
                'usage_mb' => round(($usage->total ?? 0) / 1024 / 1024, 2)
            ];
        }

        return $data;
    }

    /**
     * Get active sessions
     */
    public function getActiveSessions(Customer $customer)
    {
        if (!isset($customer->connection_config['username'])) {
            return collect();
        }

        $username = $customer->connection_config['username'];

        return RadiusAccounting::forUsername($username)
            ->activeSessions()
            ->orderBy('acctstarttime', 'desc')
            ->get();
    }

    /**
     * Get session history
     */
    public function getSessionHistory(Customer $customer, $limit = 10)
    {
        if (!isset($customer->connection_config['username'])) {
            return collect();
        }

        $username = $customer->connection_config['username'];

        return RadiusAccounting::forUsername($username)
            ->whereNotNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check FUP and enforce if needed
     */
    public function checkAndEnforceFUP(Customer $customer)
    {
        $usage = $this->getMonthlyUsage($customer);

        if (!$usage) return false;

        $package = $customer->package;

        // Check if FUP limit exceeded
        if ($package->fup_limit > 0 && $usage['fup_used_percentage'] >= 100) {
            // FUP exceeded, need to throttle speed
            return [
                'exceeded' => true,
                'current_usage' => $usage['total'],
                'limit' => $usage['fup_limit'],
                'percentage' => $usage['fup_used_percentage']
            ];
        }

        return [
            'exceeded' => false,
            'current_usage' => $usage['total'],
            'limit' => $usage['fup_limit'],
            'percentage' => $usage['fup_used_percentage']
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\UsageService;
use Illuminate\Http\Request;

class UsageController extends Controller
{
    protected $usageService;

    public function __construct(UsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function show(Customer $customer)
    {
        $this->authorize('view_customers');

        $monthlyUsage = $this->usageService->getMonthlyUsage($customer);
        $dailyChart = $this->usageService->getDailyUsageChart($customer);
        $activeSessions = $this->usageService->getActiveSessions($customer);
        $sessionHistory = $this->usageService->getSessionHistory($customer, 20);
        $fupStatus = $this->usageService->checkAndEnforceFUP($customer);

        return view('usage.show', compact(
            'customer',
            'monthlyUsage',
            'dailyChart',
            'activeSessions',
            'sessionHistory',
            'fupStatus'
        ));
    }
}

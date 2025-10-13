<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Jika URL mengandung /customer, redirect ke customer login
            if ($request->is('customer') || $request->is('customer/*')) {
                return route('customer.login');
            }

            // Default redirect ke admin login
            return route('login');
        }

        return null;
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register polymorphic relationship types
        Relation::morphMap([
            'olt' => 'App\Models\OLT',
            'odf' => 'App\Models\ODF',
            'odc' => 'App\Models\ODC',
            'joint_box' => 'App\Models\JointBox',
            'splitter' => 'App\Models\Splitter',
            'odp' => 'App\Models\ODP',
            'ont' => 'App\Models\ONT',
        ]);
    }
}

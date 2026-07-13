<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait ClearsDashboardCache
{
    /**
     * Boot the trait to hook into model events.
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            $model->clearDashboardCache();
        });

        static::deleted(function ($model) {
            $model->clearDashboardCache();
        });
    }

    /**
     * Clear the dashboard and chart caches for the relevant user.
     */
    public function clearDashboardCache()
    {
        $userId = $this->user_id ?? auth()->id();
        
        if ($userId) {
            Cache::forget("dashboard_data_{$userId}");
            
            // Clear chart data for all available periods
            $periods = [1, 3, 6, 12];
            foreach ($periods as $period) {
                Cache::forget("chart_data_{$userId}_{$period}");
            }
        }
    }
}

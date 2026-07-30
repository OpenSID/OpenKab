<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;
use Shetabit\Visitor\Models\Visit;
use Stevebauman\Location\Facades\Location;

class VisitorObserver
{
    protected static bool $updating = false;

    /**
     * Handle the Visit "created" event.
     */
    public function created(Visit $visit): void
    {
        if (static::$updating) {
            return;
        }

        try {
            $location = Location::get($visit->ip);

            if (! $location) {
                return;
            }

            static::$updating = true;

            $visit->update([
                'country_code' => $location->countryCode,
                'country' => $location->countryName,
                'region' => $location->regionCode,
                'region_name' => $location->regionName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch visitor location', [
                'ip' => $visit->ip,
                'error' => $e->getMessage(),
            ]);
        } finally {
            static::$updating = false;
        }
    }

    /**
     * Handle the Visit "updated" event.
     *
     * This method is intentionally left with a guard check to prevent
     * infinite loops when the "created" event calls update() on the model.
     * Without this guard, the update() call would trigger this method,
     * which could recursively trigger update() again if logic is added here.
     *
     * @see https://github.com/laravel/framework/issues/xxxxx
     */
    public function updated(Visit $visit): void
    {
        if (static::$updating) {
            return;
        }
    }
}

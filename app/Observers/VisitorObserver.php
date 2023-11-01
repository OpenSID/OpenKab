<?php

namespace App\Observers;

use Shetabit\Visitor\Models\Visit;
use Stevebauman\Location\Facades\Location;

class VisitorObserver
{
    /**
     * Handle the ShetabitVisitorModelsVisit "created" event.
     *
     * @param Shetabit\Visitor\Models\Visit $shetabitVisitorModelsVisit
     *
     * @return void
     */
    public function created(Visit $shetabitVisitorModelsVisit)
    {
        try {
            $location = Location::get($shetabitVisitorModelsVisit->ip);
            $shetabitVisitorModelsVisit->location = json_encode($location);
            $shetabitVisitorModelsVisit->country_code = $location->countryCode;
            $shetabitVisitorModelsVisit->country = $location->countryName;
            $shetabitVisitorModelsVisit->region = $location->regionCode;
            $shetabitVisitorModelsVisit->region_name = $location->regionName;
            $shetabitVisitorModelsVisit->save();
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}

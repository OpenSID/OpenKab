<?php

namespace App\Http\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Spatie\Activitylog\Models\Activity;

class ActivityTransformer extends TransformerAbstract
{
    public function transform(Activity $activity)
    {
        $date = Carbon::parse($activity->created_at)->setTimezone(config('app.timezone'));
        $date->settings(['formatFunction' => 'translatedFormat']);
        return [
            'id'    => $activity->id,
            'log_name' => $activity->log_name,
            'description' => $activity->description,
            'subject_id' =>  $activity->subject_id,
            'subject_type' =>  $activity->subject_type,
            'event' => $activity->event,
            'causer_name' => $activity->user?->name,
            'properties' => ! $activity->changes->isEmpty() ?  $activity->changes->toJson() : ((object) $activity->changes) ,
            'created_at' => $date->locale('id')->format('l, j F Y H:i:s')
        ];
    }
}

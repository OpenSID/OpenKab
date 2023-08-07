<?php

namespace App\Http\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Activitylog\Models\Activity;

class ActivityTransformer extends TransformerAbstract
{
    public function transform(Activity $activity)
    {
        $date = $activity->created_at;
        $date->settings(['formatFunction' => 'translatedFormat'])->timezone(config('app.timezone'));
        return [
            'id'    => $activity->id,
            'log_name' => $activity->log_name,
            'description' => $activity->description,
            'subject' =>  $activity->subject_id,
            'event' => $activity->event,
            'causer' => $activity->causer_id,
            'properties' => $activity->changes()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => $date->locale('id')->format('l, j F Y H:i:s')
        ];
    }
}

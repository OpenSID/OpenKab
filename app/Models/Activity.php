<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as ModelsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Activity extends ModelsActivity
{
    /**
     * Get the user associated with the Activity
     * Illuminate\Database\Eloquent\Relations\HasOne
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'causer_id');
    }
}

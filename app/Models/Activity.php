<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Activity as ModelsActivity;

class Activity extends ModelsActivity
{
    /**
     * Get the user associated with the Activity
     * Illuminate\Database\Eloquent\Relations\HasOne.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'causer_id');
    }
}

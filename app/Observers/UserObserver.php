<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    public function saving(User $user): void
    {
        if ($user->exists && $user->isDirty('password')) {
            $user->oldPasswordHash = $user->getOriginal('password');
        }
    }

    public function saved(User $user): void
    {
        if (!isset($user->oldPasswordHash)) {
            return;
        }

        DB::transaction(function () use ($user) {
            $reason = $user->passwordHistoryReason ?? 'password_change';

            $user->passwordHistory()->create([
                'password' => $user->oldPasswordHash,
                'reason' => $reason,
            ]);

            $historyCount = config('password.history_count', 10);
            if ($historyCount > 0) {
                $recentIds = $user->passwordHistory()
                    ->orderBy('created_at', 'desc')
                    ->limit($historyCount)
                    ->pluck('id');

                $user->passwordHistory()
                    ->whereNotIn('id', $recentIds)
                    ->delete();
            }
        });

        $user->oldPasswordHash = null;
    }
}

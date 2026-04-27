<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('pengaturan-group-read');
    }

    /**
     * Determine whether the user can view the model.
     * 
     * IDOR Prevention: User hanya bisa melihat team jika:
     * - Administrator bisa melihat semua team
     * - User lain hanya bisa melihat team yang bukan administrator
     */
    public function view(User $user, Team $team): bool
    {
        // Administrator bisa melihat semua
        if ($user->hasRole('administrator')) {
            return true;
        }

        // User biasa tidak bisa melihat team administrator
        if ($team->name === 'administrator') {
            return false;
        }

        // User bisa melihat team lain
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('pengaturan-group-create');
    }

    /**
     * Determine whether the user can update the model.
     * 
     * IDOR Prevention: User hanya bisa update team jika:
     * - Administrator bisa update semua team
     * - User lain tidak bisa update team administrator
     */
    public function update(User $user, Team $team): bool
    {
        // Administrator bisa update semua
        if ($user->hasRole('administrator')) {
            return true;
        }

        // User biasa tidak bisa update team administrator
        if ($team->name === 'administrator') {
            return false;
        }

        return $user->hasPermissionTo('pengaturan-group-edit');
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * IDOR Prevention: User hanya bisa delete team jika:
     * - Administrator bisa delete semua team (kecuali administrator team)
     */
    public function delete(User $user, Team $team): bool
    {
        // Tidak bisa delete team administrator
        if ($team->name === 'administrator') {
            return false;
        }

        return $user->hasRole('administrator') && $user->hasPermissionTo('pengaturan-group-delete');
    }
}

<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('pengaturan-users-read');
    }

    /**
     * Determine whether the user can view the model.
     * 
     * IDOR Prevention: User hanya bisa melihat user lain jika:
     * - Administrator bisa melihat semua user
     * - Superadmin daerah hanya bisa melihat user dengan kode_kabupaten yang sama
     * - User biasa hanya bisa melihat diri sendiri
     */
    public function view(User $user, User $model): bool
    {
        // Administrator bisa melihat semua
        if ($user->hasRole('administrator')) {
            return true;
        }

        // User hanya bisa melihat diri sendiri
        if ($user->id === $model->id) {
            return true;
        }

        // Superadmin daerah bisa melihat user dengan kode_kabupaten yang sama
        if (
            $user->hasRole('superadmin_daerah') &&
            $user->kode_kabupaten &&
            $user->kode_kabupaten === $model->kode_kabupaten
        ) {
            return true;
        }

        // Kabupaten bisa melihat user dengan kode_kabupaten yang sama (kecuali administrator)
        if (
            $user->hasRole('kabupaten') &&
            $user->kode_kabupaten &&
            $user->kode_kabupaten === $model->kode_kabupaten &&
            ! $model->hasRole('administrator')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('pengaturan-users-create');
    }

    /**
     * Determine whether the user can update the model.
     * 
     * IDOR Prevention: User hanya bisa update user lain jika:
     * - Administrator bisa update semua user
     * - User hanya bisa update diri sendiri
     */
    public function update(User $user, User $model): bool
    {
        // Administrator bisa update semua
        if ($user->hasRole('administrator')) {
            return true;
        }

        // User hanya bisa update diri sendiri
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * IDOR Prevention: User hanya bisa delete user lain jika:
     * - Administrator bisa delete semua user (kecuali superadmin)
     * - User tidak bisa delete user lain
     */
    public function delete(User $user, User $model): bool
    {
        // Tidak bisa delete superadmin
        if ($model->id === User::superAdmin()) {
            return false;
        }

        // Administrator bisa delete user lain (kecuali superadmin)
        return $user->hasRole('administrator') && $user->hasPermissionTo('pengaturan-users-delete');
    }

    /**
     * Determine whether the user can update the status.
     * 
     * IDOR Prevention: Hanya administrator yang bisa change status user lain
     */
    public function status(User $user, User $model): bool
    {
        // Tidak bisa change status superadmin
        if ($model->id === User::superAdmin()) {
            return false;
        }

        // Administrator bisa change status
        return $user->hasRole('administrator') && $user->hasPermissionTo('pengaturan-users-edit');
    }
}

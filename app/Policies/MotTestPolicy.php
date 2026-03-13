<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MotTest;

class MotTestPolicy
{
    /**
     * Determine if the user can view any MOT tests
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'mechanic']);
    }

    /**
     * Determine if the user can view the MOT test
     */
    public function view(User $user, MotTest $motTest): bool
    {
        return in_array($user->role, ['admin', 'manager', 'mechanic']);
    }

    /**
     * Determine if the user can create MOT tests
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'mechanic']);
    }

    /**
     * Determine if the user can update the MOT test
     */
    public function update(User $user, MotTest $motTest): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine if the user can delete the MOT test
     */
    public function delete(User $user, MotTest $motTest): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can fetch MOT history from DVSA
     */
    public function fetchHistory(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'mechanic']);
    }

    /**
     * Determine if the user can refresh vehicle MOT data from DVLA
     */
    public function refreshData(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }
}

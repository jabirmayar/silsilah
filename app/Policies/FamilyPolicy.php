<?php

namespace App\Policies;

use App\User;
use App\Family;
use Illuminate\Auth\Access\HandlesAuthorization;

class FamilyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can edit the family.
     */
    public function edit(User $user, Family $family)
    {
        return $user->id === $family->manager_id
            || is_system_admin($user);
    }

    /**
     * Determine whether the user can delete the family.
     */
    public function delete(User $user, Family $family)
    {
        return ($user->id === $family->manager_id || is_system_admin($user)) && $user->id !== $family->user_id;
    }
}

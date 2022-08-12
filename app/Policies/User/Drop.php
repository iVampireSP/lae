<?php

namespace App\Policies\User;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class Drop
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // show
    public function show(User $user, Drop $drop)
    {
        // if not admin guard and not the same user
        if (!$user->isAdmin() && $user->is($drop)) {
            return false;
        }
        return true;
    }
}

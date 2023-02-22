<?php

namespace App\Policies\WorkOrder;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReplyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): Response|bool
    {
        //

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(): Response|bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(): Response|bool
    {
        //
        return false;
    }
}

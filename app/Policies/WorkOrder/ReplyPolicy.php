<?php

namespace App\Policies\WorkOrder;

use App\Models\User;
use App\Models\WorkOrder\Reply;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReplyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        //

        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User  $user
     * @param Reply $reply
     *
     * @return Response|bool
     */
    public function update(User $user, Reply $reply): Response|bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User  $user
     * @param Reply $reply
     *
     * @return Response|bool
     */
    public function delete(User $user, Reply $reply): Response|bool
    {
        //
        return false;
    }
}

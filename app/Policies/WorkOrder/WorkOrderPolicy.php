<?php

namespace App\Policies\WorkOrder;

use App\Models\Module;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class WorkOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Module $user, WorkOrder $workOrder): Response|bool
    {
        if ($user instanceof Module) {
            return $user->id === $workOrder->module_id;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Module $user, WorkOrder $workOrder): Response|bool
    {
        if ($user instanceof Module) {
            return $user->id === $workOrder->module_id;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Module $user, WorkOrder $workOrder): Response|bool
    {
        if ($user instanceof Module) {
            return $user->id === $workOrder->module_id;
        }

        return $user->id === $workOrder->user_id
            ? Response::allow()
            : Response::deny('You do not own this work order.');
    }
}

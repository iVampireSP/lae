<?php

namespace App\Events;

use App\Models\Module;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class Users extends Event implements ShouldBroadcastNow
{
    use SerializesModels;

    public User $user;
    public string $type = 'ping';
    public string|array $data;
    public null|Module $module;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $type, $message)
    {
        $this->user = $user;
        $this->type = $type;
        $this->data = $message;

        if (Auth::guard('module')->check()) {
            $this->module = Auth::guard('module')->user();
        } else {
            $this->module = null;
        }
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.' . $this->user->id);
    }

    public function broadcastAs(): string
    {
        return 'common';
    }
}

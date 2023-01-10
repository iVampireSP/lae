<?php

namespace App\Events;

use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class Users extends Event implements ShouldBroadcastNow
{
    use SerializesModels;

    public User $user;
    public string $type = 'ping';
    public array $data;
    public null|Module $module;

    public Carbon $sent_at;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $type, array $data)
    {
        $this->user = $user;
        $this->type = $type;
        $this->data = $data;

        $this->sent_at = Carbon::now();

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

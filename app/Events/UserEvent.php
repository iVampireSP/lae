<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Auth;

class UserEvent extends Event implements ShouldBroadcastNow
{
    use SerializesModels;

    public $user_id;
    public string $type = 'ping';
    public $message;
    public $module;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $type, $message)
    {
        //
        $this->user_id = $user_id;

        $this->type = $type;

        // if message is model
        if (is_object($message)) {
            $this->message = $message->toArray();
        } else {
            $this->message = $message;
        }

        // if (Auth::check()) {

        if (Auth::guard('remote')->check()) {
            $this->module = Auth::guard('remote')->user();
        } else {
            $this->module = null;
        }
    }

    public function broadcastOn()
    {
        return new PrivateChannel('users.' . $this->user_id);
    }

    public function broadcastAs()
    {
        return 'user';
    }
}
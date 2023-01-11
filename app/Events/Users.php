<?php

namespace App\Events;

use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Users extends Event implements ShouldBroadcastNow
{
    use SerializesModels;

    public User $user;
    public string $type = 'ping';
    public array|Model $data;
    public null|Module $module = null;
    public string $event = 'messages';

    public Carbon $sent_at;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User|int $user, $type, array|Model $data)
    {
        // init vars
        $this->sent_at = Carbon::now();

        // if user is int
        if (is_int($user)) {
            $user = User::find($user);
        }

        $this->user = $user;

        $this->type = $type;

        if ($data instanceof Model) {
            $this->data = $data->toArray();
        } else {
            $this->data = $data;
        }


        // check if module
        if (Auth::guard('module')->check()) {
            $this->module = Auth::guard('module')->user();

            if (isset($this->data['event'])) {
                $this->event = $this->module->id . '.' . $this->data['event'];
            }
        }

        // log
        if (config('app.env') != 'production') {
            Log::debug('Users Event', [
                'user' => $this->user->id,
                'type' => $this->type,
                'data' => $this->data,
                'module' => $this->module,
                'event' => $this->event,
            ]);
        }
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.' . $this->user->id);
    }

    public function broadcastAs(): string
    {
        return 'messages';
    }
}

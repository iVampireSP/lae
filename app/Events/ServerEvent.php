<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ServerEvent extends Event implements ShouldBroadcast
{

    public array $data;

    public string $type = 'servers.updated';

    public function __construct($servers)
    {
        $this->data = $servers;
    }

    public function broadcastOn()
    {
        return new Channel('servers');
    }

    public function broadcastAs()
    {
        return 'servers';
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class AvailableDrivers implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $drivers;

    public function __construct($drivers)
    {
        $this->drivers = $drivers;
    }

    public function broadcastOn()
    {
        return new Channel('available-drivers');
    }

    public function broadcastAs(): string
    {
        return 'AvailableDriversUpdated';
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;


class TestEvent implements ShouldBroadcast
{
    public $data;
    public $userId;

    public function __construct($data, $userId)
    {
        $this->data = $data;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        Log::info('BROADCAST EXECUTADO', [
            'user_id' => $this->userId,
            'data' => $this->data
        ]);

        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->data,
            'user_id' => $this->userId,
        ];
    }

    public function broadcastAs()
    {
        return 'test.event';
    }
}

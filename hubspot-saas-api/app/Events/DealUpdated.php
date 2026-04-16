<?php

namespace App\Events;

use App\Models\Deal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $deal;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal->load(['client', 'company']);
    }

    public function broadcastOn()
    {
        return new Channel('deals');
    }

    public function broadcastAs()
    {
        return 'deal.updated';
    }
}

<?php

namespace Tv2regionerne\StatamicCuratedCollection\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CuratedCollectionUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tag;

    /**
     * Create a new event instance.
     */
    public function __construct($tag)
    {
        $this->tag = $tag;
    }
}

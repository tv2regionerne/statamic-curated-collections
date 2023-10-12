<?php

namespace Tv2regionerne\StatamicCuratedCollection\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CuratedCollectionTagEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tag;

    /**
     * Create a new event instance.
     */
    public function __construct(string $tag)
    {
        $this->tag = strtolower($tag);
    }
}

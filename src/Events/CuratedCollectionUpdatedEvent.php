<?php

namespace Tv2regionerne\StatamicCuratedCollection\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Statamic\Events\Event;

class CuratedCollectionUpdatedEvent extends Event implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public string $handle;

    /**
     * Create a new event instance.
     */
    public function __construct(string $handle)
    {
        $this->handle = strtolower($handle);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('curated-collections-private.'.$this->handle);
    }

    public function broadcastAs()
    {
        return 'CuratedCollections.CuratedCollectionUpdated';
    }
}

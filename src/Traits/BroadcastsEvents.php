<?php

namespace FluxErp\Traits;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Database\Eloquent\BroadcastsEvents as BaseBroadcastsEvents;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use ReflectionClass;
use Spatie\ModelInfo\Relations\Relation;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

trait BroadcastsEvents
{
    use BaseBroadcastsEvents, InteractsWithSockets;

    public function broadcastChannel(bool $generic = true): string
    {
        $default = parent::broadcastChannel();

        // Remove the id from the channel to get a non id specific channel.
        $broadcastChannelGeneric = explode('.', $default);
        array_pop($broadcastChannelGeneric);
        $broadcastChannelGeneric = implode('.', $broadcastChannelGeneric);

        return $generic ?
            $broadcastChannelGeneric :
            $default;
    }

    public static function getBroadcastChannelRoute(): string
    {
        $reflection = new ReflectionClass(self::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        return $instance->broadcastChannelRoute();
    }

    public static function getBroadcastChannel(): string
    {
        $reflection = new ReflectionClass(self::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        return $instance->broadcastChannel();
    }

    protected function newBroadcastableEvent($event): BroadcastableModelEventOccurred
    {
        return (new BroadcastableModelEventOccurred($this, $event))->dontBroadcastToCurrentUser();
    }

    public function broadcastOn($event): array
    {
        $channels[] = new PrivateChannel($this->broadcastChannel());
        if ($event === 'updated') {
            $channels[] = new PrivateChannel($this->broadcastChannel(false));
        }

        $relationshipTypes = [
            BelongsTo::class,
            HasOne::class,
            HasOneThrough::class,
            MorphOne::class,
        ];

        ModelInfo::forModel($this)
            ->relations
            ->filter(fn (Relation $relation) => in_array($relation->type, $relationshipTypes))
            ->each(function ($relation) use (&$channels) {
                $relationChannel = $this->{$relation->name}()->first()?->broadcastChannel(false);

                if ($relationChannel) {
                    $channels[] = new PrivateChannel($relationChannel);
                }
            });

        return $channels;
    }
}

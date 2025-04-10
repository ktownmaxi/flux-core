<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\EventSubscription\CreateEventSubscriptionRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateEventSubscription extends FluxAction
{
    public static function models(): array
    {
        return [EventSubscription::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateEventSubscriptionRuleset::class;
    }

    public function performAction(): EventSubscription
    {
        $eventSubscription = app(EventSubscription::class, ['attributes' => $this->data]);
        $eventSubscription->save();

        return $eventSubscription->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['subscribable_id'] ??= Auth::id();
        $this->data['subscribable_type'] ??= Auth::user()->getMorphClass();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $eventClass = Helper::classExists(classString: ucfirst($this->data['event']), isEvent: true);

        if ($this->data['event'] !== '*' && ! $eventClass) {
            $eventExploded = explode(':', str_replace(' ', '', $this->data['event']));
            $model = $eventExploded[1] ?? null;
            $eloquentEvent = $model ? eloquent_model_event($eventExploded[0], $model) : null;
        } else {
            $eloquentEvent = $this->data['event'];
        }

        if (! $eventClass && ! $eloquentEvent) {
            throw ValidationException::withMessages([
                'event' => [__('Event not found')],
            ]);
        }

        $this->data['event'] = $eventClass ?: $eloquentEvent;

        if (resolve_static(EventSubscription::class, 'query')
            ->where('event', $this->data['event'])
            ->where('subscribable_type', $this->data['subscribable_type'])
            ->where('subscribable_id', $this->data['subscribable_id'])
            ->where('model_type', $this->data['model_type'])
            ->where(function (Builder $query) {
                return $query->where('model_id', $this->data['model_id'])
                    ->orWhereNull('model_id');
            })
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'subscription' => [__('Already subscribed')],
            ])->errorBag('createEventSubscription');
        }
    }
}

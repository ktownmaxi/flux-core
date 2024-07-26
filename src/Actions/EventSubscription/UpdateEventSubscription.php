<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\EventSubscription\UpdateEventSubscriptionRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateEventSubscription extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateEventSubscriptionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [EventSubscription::class];
    }

    public function performAction(): Model
    {
        $eventSubscription = resolve_static(EventSubscription::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $eventSubscription->fill($this->data);
        $eventSubscription->save();

        return $eventSubscription->withoutRelations()->fresh();
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
        $this->data['user_id'] ??= Auth::id();

        if (resolve_static(EventSubscription::class, 'query')
            ->whereKeyNot($this->data['id'])
            ->where('event', $this->data['event'])
            ->where('user_id', $this->data['user_id'])
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

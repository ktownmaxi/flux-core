<?php

namespace FluxErp\Livewire\Features\Calendar;

use FluxErp\Contracts\Calendarable;
use FluxErp\Facades\Action;
use FluxErp\Helpers\Helper;
use FluxErp\Livewire\Forms\CalendarEventForm;
use FluxErp\Livewire\Forms\CalendarForm;
use FluxErp\Models\Address;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\Calendar\CalendarComponent;

class FluxCalendar extends CalendarComponent
{
    public string $tab = 'users';

    public string $search = '';

    public array $searchResults = [];

    public CalendarForm $calendar;

    public CalendarEventForm $event;

    protected string $view = 'flux::livewire.features.calendar.flux-calendar';

    public function getMyCalendars(): Collection
    {
        $calendarables = model_info_all()
            ->filter(fn ($modelInfo) => in_array(Calendarable::class, $modelInfo->implements))
            ->map(fn ($modelInfo) => resolve_static($modelInfo->class, 'toCalendar'));

        return parent::getMyCalendars()->isEmpty() ?
            $calendarables->merge(parent::getMyCalendars()) : parent::getMyCalendars()->merge($calendarables);
    }

    public function getEvents(array $info, array $calendarAttributes): array
    {
        if ($calendarAttributes['model_type'] ?? false) {
            return Relation::getMorphedModel($calendarAttributes['model_type'])::query()
                ->get()
                ->map(fn (Model $model) => $model->toCalendarEvent())
                ->toArray();
        }

        return parent::getEvents($info, $calendarAttributes);
    }

    #[Renderless]
    public function saveCalendar(array $attributes): array|false
    {
        if ($attributes['model_type'] ?? false) {
            return false;
        }

        try {
            $this->calendar->reset();
            $this->calendar->fill($attributes);
            $this->calendar->user_id = auth()->id();
            $this->calendar->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $result = $this->calendar->getActionResult()?->toArray();

        if (! $result) {
            return false;
        }

        return array_merge(
            $result,
            ['resourceEditable' => $result['is_editable'] ?? false]
        );
    }

    #[Renderless]
    public function saveEvent(array $attributes): array|false
    {
        $attributes['is_all_day'] = $attributes['allDay'] ?? false;
        $attributes['confirm_option'] = ! $this->calendarEventWasRepeatable ? 'all' : $this->confirmSave;

        if ($attributes['has_repeats'] ?? false) {
            $attributes['repeat'] = [
                'start' => $attributes['start'],
                'interval' => $attributes['interval'] ?? null,
                'unit' => $attributes['unit'] ?? null,
                'weekdays' => $attributes['weekdays'] ?? null,
                'monthly' => $attributes['monthly'] ?? null,
            ];
        }

        if ($attributes['calendar_type'] ?? false) {
            $action = Action::get(
                $attributes['calendar_type'] . (($attributes['id'] ?? false) ? '.update' : '.create')
            );

            if (! $action) {
                return false;
            }

            $modelClass = Relation::getMorphedModel($attributes['calendar_type']);

            try {
                $result = $action['class']::make(resolve_static($modelClass, 'fromCalendarEvent', [$attributes]))
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }

            $result = $result->toCalenderEvent();
        } else {
            try {
                $this->event->reset();
                $this->event->fill($attributes);
                $this->event->original_start = data_get($this->oldCalendarEvent, 'start');
                $this->event->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return false;
            }

            $result = array_values($this->event->getActionResult());
        }

        $result = array_map(
            function ($event) use ($attributes) {
                if ($event instanceof CalendarEvent) {
                    return $event->toCalendarEventObject([
                        'is_editable' => true,
                        'is_repeatable' => $attributes['is_repeatable'] ?? false,
                        'has_repeats' => ! is_null($event->repeat),
                    ]);
                }

                return $event;
            },
            Helper::getRepetitions($result, $this->calendarPeriod['start'], $this->calendarPeriod['end'])
        );

        return $result ?: false;
    }

    public function deleteCalendar(array $attributes): bool
    {
        $attributes['confirm_option'] = ! $this->calendarEventWasRepeatable ? 'all' : $this->confirmDelete;

        try {
            $this->calendar->reset();
            $this->calendar->fill($attributes);
            $this->calendar->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    #[Renderless]
    public function deleteEvent(array $attributes): array|false
    {
        $attributes['confirm_option'] = ! $this->calendarEventWasRepeatable ? 'all' : $this->confirmDelete;

        if ($attributes['calendar_type'] ?? false) {
            $action = Action::get($attributes['calendar_type'] . '.delete');

            if (! $action) {
                return false;
            }

            $modelClass = Relation::getMorphedModel($attributes['calendar_type']);

            try {
                $action['class']::make(resolve_static($modelClass, 'fromCalendarEvent', [$attributes]))
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        } else {
            try {
                $this->event->reset();
                $this->event->fill($attributes);
                $this->event->delete();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        }

        return [
            'id' => $attributes['id'],
            'confirmOption' => $attributes['confirm_option'],
            'repetition' => $attributes['repetition'] ?? null,
        ];
    }

    #[Renderless]
    public function updatedCalendarEventStatus($value): void
    {
        $calendarEvent = app(CalendarEvent::class)->query()
            ->whereKey($this->calendarEvent['id'])
            ->firstOrFail();

        $this->inviteStatus($calendarEvent, $value, $calendarEvent->calendar_id);
    }

    #[Renderless]
    public function updatedSearch(): void
    {
        $model = app($this->tab === 'users' ? User::class : Address::class);
        $this->searchResults = $this->search ? $model::search($this->search)->get()->toArray() : [];
    }

    #[Renderless]
    public function updatedTab(): void
    {
        $this->search = '';
        $this->searchResults = [];
    }

    #[Renderless]
    public function addInvitedRecord(int $id): void
    {
        $model = app($this->tab === 'users' ? User::class : Address::class);

        $this->addInvitee($model->query()->whereKey($id)->first());
    }

    public function render(): View
    {
        return view($this->view);
    }

    #[Renderless]
    private function addInvitee(?Model $model): void
    {
        if (is_null($model)) {
            return;
        }

        $array = Arr::only($model->toArray(), ['id', 'name']);

        if ($model instanceof Address) {
            $this->calendarEvent['invited_addresses'][] = $array;
        } else {
            $this->calendarEvent['invited_users'][] = $array;
        }
    }
}

<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Models\Pivots\CalendarEventInvite;
use FluxErp\Models\Pivots\Inviteable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

class CalendarEvent extends FluxModel implements HasMedia
{
    use HasPackageFactory, HasUlids, HasUserModification, InteractsWithMedia, LogsActivity;

    protected function casts(): array
    {
        return [
            'start' => 'datetime',
            'end' => 'datetime',
            'repeat_start' => 'datetime',
            'repeat_end' => 'datetime',
            'excluded' => 'array',
            'is_all_day' => 'boolean',
            'has_taken_place' => 'boolean',
            'extended_props' => 'array',
        ];
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Inviteable::class);
    }

    public function invited(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'inviteable')
            ->using(CalendarEventInvite::class)
            ->withPivot(['status', 'model_calendar_id']);
    }

    public function invitedAddresses(): MorphToMany
    {
        return $this->morphedByMany(Address::class, 'inviteable', 'inviteables')
            ->using(CalendarEventInvite::class)
            ->withPivot(['status', 'model_calendar_id']);
    }

    public function toCalendarEventObject(array $attributes = []): array
    {
        $attributes = array_merge(
            [
                'calendar_type' => $this->calendar()->value('model_type'),
                'extendedProps' => array_filter($this->extended_props ?? [], fn ($item) => ! is_array($item)),
            ],
            $attributes
        );

        $customProperties = array_map(
            fn ($item) => array_merge($item, ['value' => null]),
            $this->calendar()->value('custom_properties') ?? []
        );

        if ($this->repeat) {
            $repeatable = explode(',', $this->repeat);
            $interval = null;
            $repeat = match (true) {
                str_contains($repeatable[0], 'year') => [
                    'unit' => 'years',
                ],
                str_contains($repeatable[0], 'month') => [
                    'unit' => 'months',
                    'monthly' => str_contains($repeatable[0], 'of') ? explode(' ', $repeatable[0])[0] : 'day',
                ],
                str_contains($repeatable[0], 'week') => [
                    'unit' => 'weeks',
                ],
                str_contains($repeatable[0], 'day') => [
                    'unit' => 'days',
                ],
                default => [
                    'unit' => null,
                ]
            };

            preg_match('~\+(.*?) ~', $repeatable[0], $interval);

            if ($repeat['unit'] === 'weeks') {
                $repeat['interval'] = ! is_bool($interval[1] ?? false) ? $interval[1] + 1 : null;
                $repeat['weekdays'] = array_map(
                    fn ($item) => trim(explode(' ', explode('+', $item)[0])[1]),
                    $repeatable
                );
            } else {
                $repeat['interval'] = $interval[1] ?? null;
            }
        }

        $calendarEventObject = array_merge(
            [
                'id' => data_get($this->attributes, 'id', $this->ulid),
                'calendar_id' => $this->calendar_id,
                'model_type' => $this->model_type,
                'model_id' => $this->model_id,
                'start' => $this->start->format('Y-m-d\TH:i:s.u'),
                'end' => $this->end?->format('Y-m-d\TH:i:s.u'),
                'title' => $this->title,
                'description' => $this->description,
                'repeat_end' => $this->repeat_end?->format('Y-m-d'),
                'recurrences' => $this->recurrences,
                'allDay' => $this->is_all_day,
                'has_taken_place' => $this->has_taken_place,
                'editable' => ! $this->calendar->is_public && ! $this->is_invited,
                'is_editable' => ! $this->calendar->is_public && ! $this->is_invited,
                'is_invited' => $this->is_invited,
                'is_public' => $this->calendar->is_public,
                'status' => $this->status ?: 'busy',
                'invited' => $this->invited->toArray(),
                'interval' => $repeat['interval'] ?? null,
                'unit' => $repeat['unit'] ?? null,
                'weekdays' => $repeat['weekdays'] ?? [],
                'monthly' => $repeat['monthly'] ?? 'day',
                'repeat_radio' => $this->repeat_end ? 'repeat_end' : ($this->recurrences ? 'recurrences' : null),
            ],
            $attributes
        );

        $calendarEventObject['customProperties'] = Arr::keyBy(
            array_filter($this->extended_props ?? [], fn ($item) => is_array($item)),
            'name'
        );

        foreach ($customProperties as $customProperty) {
            if (! array_key_exists($customProperty['name'], $calendarEventObject['customProperties'])) {
                $calendarEventObject['customProperties'][$customProperty['name']] = $customProperty;
            }
        }

        return $calendarEventObject;
    }

    public function fromCalendarEventObject(array $calendarEvent): static
    {
        $mappedArray = [];

        foreach ($calendarEvent as $key => $value) {
            $mappedArray[Str::snake($key)] = $value;
        }

        $mappedArray['is_all_day'] = $calendarEvent['allDay'] ?? false;

        foreach ($mappedArray['extended_props'] ?? [] as $key => $value) {
            $mappedArray[Str::snake($key)] = $mappedArray[Str::snake($key)] ?? $value;
        }

        if ($mappedArray['has_repeats'] ?? false) {
            // Build repeat string
            if (in_array($mappedArray['unit'], ['days', 'years'])
                || ($mappedArray['unit'] === 'months' && ($mappedArray['monthly'] ?? false) === 'day')
            ) {
                $mappedArray['repeat'] = '+' . $mappedArray['interval'] . ' ' . $mappedArray['unit'];
            } elseif ($mappedArray['unit'] === 'weeks') {
                $mappedArray['repeat'] = implode(',', array_map(
                    fn ($item) => 'next ' . $item . ' +' . $mappedArray['interval'] - 1 . ' ' . $mappedArray['unit'],
                    array_intersect(
                        array_map(
                            fn ($item) => Carbon::parse($mappedArray['start'])->addDays($item)->format('D'),
                            range(0, 6)
                        ),
                        $mappedArray['weekdays'],
                    )
                ));
            } elseif ($mappedArray['unit'] === 'months') {
                $mappedArray['repeat'] = $mappedArray['monthly'] . ' '
                    . Carbon::parse($mappedArray['start'])->format('D') . ' of +'
                    . $mappedArray['interval'] . ' ' . $mappedArray['unit'];
            }
        }

        switch ($mappedArray['repeat_radio']) {
            case 'repeat_end':
                $mappedArray['recurrences'] = null;
                break;
            case 'recurrences':
                $mappedArray['repeat_end'] = null;
                break;
            default:
                $mappedArray['recurrences'] = null;
                $mappedArray['repeat_end'] = null;
                break;
        }

        $this->fill($mappedArray);

        return $this;
    }

    public function invitedModels(): Collection
    {
        $types = $this->invites()->distinct('inviteable_type')->pluck('inviteable_type')->toArray();

        $invitedModels = collect();
        foreach ($types as $type) {
            $invitedModels = $invitedModels->merge(
                $this->morphedByMany(
                    Relation::getMorphedModel($type), 'inviteable')->withPivot('status')->get()
            );
        }

        return $invitedModels;
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }
}

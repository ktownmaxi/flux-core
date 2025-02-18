<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Casts\TimeDuration;
use FluxErp\Contracts\Calendarable;
use FluxErp\States\Project\ProjectState;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\HasFrontendAttributes;

class Project extends FluxModel implements Calendarable, HasMedia, InteractsWithDataTables
{
    use Commentable, Filterable, HasAdditionalColumns, HasClientAssignment, HasFrontendAttributes, HasPackageFactory,
        HasParentChildRelations, HasSerialNumberRange, HasStates, HasTags, HasUserModification, HasUuid,
        InteractsWithMedia, LogsActivity, Searchable, SoftDeletes;

    protected ?string $detailRouteName = 'projects.id';

    protected static string $iconName = 'briefcase';

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (! $project->project_number) {
                $project->getSerialNumber('project_number');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'state' => ProjectState::class,
            'time_budget' => TimeDuration::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getLabel(): ?string
    {
        return $this->name . ' (' . $this->project_number . ')';
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb')
            ?: $this->contact?->getFirstMediaUrl('avatar', 'thumb')
            ?: static::icon()->getUrl();
    }

    public function calculateProgress(): void
    {
        $this->progress = bcdiv(
            $this->tasks()->sum('progress'),
            $this->tasks()->count()
        );
        $this->total_cost = $this->tasks()->sum('total_cost');

        $this->save();

        if ($this->order) {
            $this->order->calculateMargin()->save();
        }
    }

    public static function toCalendar(): array
    {
        return [
            'id' => Str::of(static::class)->replace('\\', '.'),
            'modelType' => morph_alias(static::class),
            'name' => __('Projects'),
            'color' => '#813d9c',
            'resourceEditable' => false,
            'hasRepeatableEvents' => false,
            'isPublic' => false,
            'isShared' => false,
            'permission' => 'owner',
            'group' => 'other',
            'isVirtual' => true,
        ];
    }

    public function toCalendarEvent(?array $info = null): array
    {
        return [
            'id' => $this->id,
            'calendar_type' => $this->getMorphClass(),
            'title' => $this->name,
            'start' => ($this->start_date ?? $this->created_at)->toDateTimeString(),
            'end' => $this->end_date?->endOfDay()->toDateTimeString(),
            'status' => $this->state::$name,
            'invited' => [],
            'description' => $this->description,
            'extendedProps' => [
                'appendTitle' => $this->state->badge(),
            ],
            'allDay' => true,
            'is_editable' => true,
            'is_invited' => false,
            'is_public' => false,
            'is_repeatable' => false,
        ];
    }

    public static function fromCalendarEvent(array $event): Model
    {
        $project = new static();
        $project->forceFill([
            'id' => data_get($event, 'id'),
            'name' => data_get($event, 'title'),
            'start_date' => data_get($event, 'start'),
            'end_date' => data_get($event, 'end'),
            'description' => data_get($event, 'description'),
        ]);

        return $project;
    }

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?array $info = null
    ): void {
        $builder->where(function (Builder $query) use ($start, $end) {
            $query->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhereBetween('created_at', [$start, $end]);
        });
    }
}

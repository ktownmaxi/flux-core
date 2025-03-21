<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Livewire\Component;

/**
 * @deprecated
 */
class CustomEvents extends Component
{
    use Actions;

    public ?array $additionalData = [];

    public array $customEvents = [];

    public string|Model $model;

    public ?int $modelId;

    public array|Model|null $record;

    public function mount(
        string|Model $model,
        ?int $id = null,
        ?array $record = null,
        ?string $recordModel = null,
        ?array $additionalData = null
    ): void {
        $this->model = $model;
        $this->modelId = $id;

        $modelAsRecord = false;
        if ($record && $recordModel) {
            $this->record = $recordModel::query()
                ->whereKey($record['id'] ?? null)
                ->firstOrNew();
            $this->record->fill($record);
        } elseif ($record) {
            $this->record = $record;
        } else {
            $modelAsRecord = true;
        }

        if ($this->model instanceof Model) {
            $this->customEvents = $this->model->customEvents?->toArray() ?? [];
            if ($modelAsRecord) {
                $this->record = $this->model;
            }
        } elseif ($this->modelId) {
            $modelInstance = $model::query()->whereKey($this->modelId)->first();
            if ($modelAsRecord) {
                $this->record = $modelInstance ?: [];
            }

            $this->customEvents = $modelInstance?->customEvents?->toArray() ?? [];
        }

        $this->additionalData = $additionalData ?? [];
    }

    public function render(): View
    {
        return view('flux::livewire.features.custom-events');
    }

    public function dispatchCustomEvent(string $event, ...$additionalData): void
    {
        Event::dispatch($event, empty($additionalData) ? $this->record : $additionalData);

        $this->notification()->success(__('Event dispatched: ') . $event)->send();
    }
}

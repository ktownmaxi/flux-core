<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Task\TaskList;
use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class Tasks extends TaskList
{
    #[Modelable]
    public int $modelId;

    protected ?string $modelType = Address::class;

    public function save(): bool
    {
        $this->task->model_type = morph_alias($this->modelType);
        $this->task->model_id = $this->modelId;

        return parent::save();
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('model_type', morph_alias($this->modelType))
            ->where('model_id', $this->modelId);
    }
}

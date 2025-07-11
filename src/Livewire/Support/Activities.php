<?php

namespace FluxErp\Livewire\Support;

use FluxErp\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithPagination;
use TeamNiftyGmbH\DataTable\Helpers\Icon;

abstract class Activities extends Component
{
    use WithPagination;

    #[Locked]
    public array $activities = [];

    #[Modelable]
    public ?int $modelId = null;

    public int $page = 1;

    public int $perPage = 15;

    public int $total = 0;

    protected string $modelType;

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.support.activities');
    }

    public function loadData(): void
    {
        if (! $this->modelType || ! $this->modelId) {
            return;
        }

        $activities = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->firstOrFail()
            ->activities()
            ->with('causer:id,name')
            ->latest('id')
            ->paginate(perPage: $this->perPage * $this->page);

        $this->perPage = $activities->perPage();
        $this->total = $activities->total();

        $this->activities = $activities
            ->map(function ($item) {
                $itemArray = $item->toArray();
                $itemArray['causer']['name'] = $item->causer?->getLabel() ?: __('Unknown');
                $itemArray['causer']['avatar_url'] = $item->causer?->getAvatarUrl() ?: Icon::make('user')->getUrl();
                $itemArray['event'] = __($item->event);

                if (! auth()->user() instanceof User) {
                    $itemArray['properties'] = [
                        'old' => [],
                        'attributes' => [],
                    ];
                }

                return $itemArray;
            })
            ->toArray();
    }

    public function updatedPage(): void
    {
        $this->loadData();
    }
}

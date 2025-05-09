<?php

namespace FluxErp\Livewire\Features;

use Exception;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class SearchBar extends Component
{
    use Actions, WithPagination;

    public ?array $load = null;

    public array $modelLabels = [];

    public array $return = [];

    public string $search = '';

    public array|string $searchModel = '';

    public bool $show = false;

    public function mount(): void
    {
        if ($this->searchModel === '') {
            $this->searchModel = model_info_all()
                ->filter(fn (ModelInfo $modelInfo) => in_array(
                    Searchable::class,
                    class_uses_recursive($modelInfo->class)
                )
                    && method_exists($modelInfo->class, 'detailRoute')
                    && (
                        method_exists($modelInfo->class, 'getLabel')
                        || ! is_null($modelInfo->attribute('name'))
                    )
                )
                ->map(fn ($modelInfo) => $modelInfo->class)
                ->toArray();
        }

        foreach ((array) $this->searchModel as $searchModel) {
            $this->modelLabels[$searchModel] = [
                'label' => __(Str::plural(class_basename($searchModel))),
                'icon' => method_exists($searchModel, 'icon') ? $searchModel::icon()->getSvg() : null,
            ];
        }
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.search-bar', ['results' => $this->return]);
    }

    #[Renderless]
    public function showDetail(string $model, int $id): void
    {
        /** @var Model $model */
        $modelInstance = resolve_static($model, 'query')->whereKey($id)->first();

        if (! $modelInstance) {
            $this->notification()->error(__('Record not found'))->send();

            return;
        }

        $this->redirect($modelInstance->detailRoute(), true);
    }

    public function updatedSearch(): void
    {
        if ($this->search) {
            if (is_array($this->searchModel)) {
                $return = [];
                foreach ($this->searchModel as $model) {
                    try {
                        $result = resolve_static($model, 'search', ['query' => $this->search])
                            ->toEloquentBuilder()
                            ->latest()
                            ->limit(5)
                            ->get()
                            ->filter(fn ($item) => $item->detailRoute())
                            ->map(fn (Model $item) => [
                                'id' => $item->getKey(),
                                'label' => method_exists($item, 'getLabel') ?
                                    $item->getLabel() : $item->getAttribute('name'),
                                'src' => method_exists($item, 'getAvatarUrl') ? $item->getAvatarUrl() : null,
                            ]);

                        if (count($result)) {
                            $return[$model] = collect($result)->toArray();
                        }
                    } catch (Exception) {
                        // ignore
                    }

                    if (count($return) >= 10) {
                        break;
                    }
                }

                $this->return = $return;
            } else {
                $result = resolve_static($this->searchModel, 'search', ['query' => $this->search])
                    ->paginate();

                if ($this->load && $result && $result instanceof LengthAwarePaginator) {
                    $result->load($this->load);
                }

                $this->return = count($result->items()) ? $result->items() : null;
                $this->show = true;
            }
        } else {
            $this->return = [];
        }

        $this->skipRender();
    }
}

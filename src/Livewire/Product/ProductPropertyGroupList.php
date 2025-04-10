<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\ProductPropertyGroupList as BaseProductPropertyGroupList;
use Illuminate\View\ComponentAttributeBag;

class ProductPropertyGroupList extends BaseProductPropertyGroupList
{
    public bool $hasSidebar = false;

    public ?bool $isSearchable = true;

    public bool $showFilterInputs = false;

    public function startSearch(): void
    {
        $this->filters = [[
            'name',
            'like',
            '%' . $this->search . '%',
        ]];

        parent::startSearch();
    }

    protected function allowSoftDeletes(): bool
    {
        return false;
    }

    protected function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-bind:class' => <<<'JS'
                record.id === productPropertyGroup?.id && 'bg-indigo-100 dark:bg-indigo-800'
            JS,
        ]);
    }
}

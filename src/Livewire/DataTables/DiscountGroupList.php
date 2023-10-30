<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\DiscountGroup;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class DiscountGroupList extends DataTable
{
    protected string $model = DiscountGroup::class;

    public array $enabledCols = [
        'name',
        'is_active',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];

    public function getDiscounts(DiscountGroup $discountGroup): array
    {
        $this->skipRender();

        $discounts = $discountGroup->discounts()
            ->with('model')
            ->get()
            ->map(function ($discount) {
                $discount->model_type = __(class_basename($discount->model_type));

                return $discount;
            })
            ->toArray();

        return $discounts;
    }
}

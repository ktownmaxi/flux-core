<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;

class ContactAllDiscountsList extends DiscountList
{
    protected string $model = Discount::class;

    public bool $isFilterable = false;

    public int $contactId;

    /**
     * @return void
     */
    public function mount(): void
    {
        parent::mount();
    }

    public function loadData(): void
    {
        $this->initialized = true;

        $this->setData(Contact::query()
            ->whereKey($this->contactId)->firstOrFail()
            ->getAllDiscounts()
            ->each(fn(Discount $discount) => $discount->load('model'))
            ->map(fn(Discount $discount) => $this->itemToArray($discount))
            ->toArray());
    }
}

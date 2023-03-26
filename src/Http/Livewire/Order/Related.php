<?php

namespace FluxErp\Http\Livewire\Order;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Related extends Component
{
    public int $orderId;

    public ?int $parentId;

    public function mount(int $id): void
    {
        $this->orderId = $id;

        $this->parentId = \FluxErp\Models\Order::query()->whereKey($id)->first()?->parent_id;
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.order.related');
    }
}

<?php

namespace FluxErp\Tests\Livewire\Portal\DataTables;

use FluxErp\Livewire\Portal\DataTables\OrderList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrderListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(OrderList::class)
            ->assertStatus(200);
    }
}

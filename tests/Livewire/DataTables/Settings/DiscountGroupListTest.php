<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\DataTables\Settings\DiscountGroupList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class DiscountGroupListTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(DiscountGroupList::class)
            ->assertStatus(200);
    }
}

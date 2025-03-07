<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\Product\MediaGrid;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class MediaGridTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(MediaGrid::class)
            ->assertStatus(200);
    }
}

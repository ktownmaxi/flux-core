<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\TicketTypeEdit;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TicketTypeEditTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(TicketTypeEdit::class)
            ->assertStatus(200);
    }
}

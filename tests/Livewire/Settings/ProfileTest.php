<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Profile;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProfileTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Profile::class)
            ->assertStatus(200);
    }
}

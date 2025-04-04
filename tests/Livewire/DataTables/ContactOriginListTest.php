<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ContactOriginList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ContactOriginListTest extends TestCase
{
    protected string $livewireComponent = ContactOriginList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

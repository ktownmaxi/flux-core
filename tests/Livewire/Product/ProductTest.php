<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\Product as ProductView;
use FluxErp\Models\Client;
use FluxErp\Models\Currency;
use FluxErp\Models\Product;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $this->product = Product::factory()
            ->hasAttached(factory: $dbClient, relationship: 'clients')
            ->create();

        Currency::factory()->create(['is_default' => true]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(ProductView::class, ['id' => $this->product->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs(): void
    {
        Livewire::test(ProductView::class, ['id' => $this->product->id])->cycleTabs();
    }
}

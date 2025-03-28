<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Models\SerialNumber;

class ProductsSerialNumbersTest extends BaseSetup
{
    private SerialNumber $serialNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serialNumber = SerialNumber::factory()->create();
    }

    public function test_products_id_serial_numbers_no_user(): void
    {
        $this->get('/products/serial-numbers/' . $this->serialNumber->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_id_serial_numbers_page(): void
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
            ->assertStatus(200);
    }

    public function test_products_id_serial_numbers_serial_number_not_found(): void
    {
        $this->serialNumber->delete();

        $this->user->givePermissionTo(
            Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
            ->assertStatus(404);
    }

    public function test_products_id_serial_numbers_without_permission(): void
    {
        Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
            ->assertStatus(403);
    }

    public function test_products_serial_numbers_no_user(): void
    {
        $this->get('/products/serial-numbers')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_serial_numbers_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('products.serial-numbers.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
            ->assertStatus(200);
    }

    public function test_products_serial_numbers_without_permission(): void
    {
        Permission::findOrCreate('products.serial-numbers.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
            ->assertStatus(403);
    }
}

<?php

namespace FluxErp\Tests\Browser\Portal;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;

class OrdersTest extends PortalDuskTestCase
{
    use DatabaseTruncation;

    public Collection $orders;

    protected function setUp(): void
    {
        parent::setUp();

        $priceList = PriceList::factory()->create([
            'is_net' => false,
        ]);

        $contacts = Contact::factory()->count(2)->create([
            'price_list_id' => $priceList->id,
            'client_id' => $this->dbClient->getKey(),
        ]);

        $currency = Currency::factory()->create();
        Currency::query()->first()->update(['is_default' => true]);

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $addresses = Address::factory()->count(2)->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contacts->random()->id,
        ]);

        Order::factory()->count(3)->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'contact_id' => $contacts->random()->id,
            'address_invoice_id' => $addresses->random()->id,
            'address_delivery_id' => $addresses->random()->id,
            'is_locked' => true,
        ]);

        $this->orders = Order::factory()->count(3)->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'contact_id' => $this->user->contact_id,
            'address_invoice_id' => $this->user->id,
            'address_delivery_id' => $this->user->id,
            'is_locked' => true,
        ]);
    }

    public function test_can_see_order_details(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openMenu();
            $browser
                ->click('nav [href="/orders"]')
                ->waitForRoute('portal.orders');

            $browser->waitFor('[tall-datatable] tbody [data-id]');

            $rows = $browser->elements('[tall-datatable] tbody [data-id]');

            $rows[0]->click();

            $browser->waitForRoute('portal.orders.id', ['id' => $this->orders[2]->id])
                ->assertRouteIs('portal.orders.id', ['id' => $this->orders[2]->id]);
        });
    }

    public function test_can_see_orders(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit($this->baseUrl())->type('email', $this->user->email)
                ->type('password', $this->password)
                ->press('Login')
                ->waitForReload()
                ->assertRouteIs('portal.dashboard');
            $this->openMenu();

            $browser->click('nav [href="/orders"]')
                ->waitForRoute('portal.orders')
                ->assertRouteIs('portal.orders')
                ->waitForText('My orders')
                ->waitForText('Order Number')
                ->waitForText('Order Type -> Name')
                ->waitForText('Commission')
                ->waitForText('Payment State')
                ->assertSee('Order Number')
                ->assertSee('Order Type -> Name')
                ->assertSee('Commission')
                ->assertSee('Payment State')
                ->assertScript('document.body.innerText.includes("Total Net Price") || document.body.innerText.includes("Total Gross Price")')
                ->assertScript('!(document.body.innerText.includes("Total Net Price") && document.body.innerText.includes("Total Gross Price"))');

            $rows = $browser->elements('[tall-datatable] tbody [data-id]');

            $this->assertCount(3, $rows);
        });
    }
}

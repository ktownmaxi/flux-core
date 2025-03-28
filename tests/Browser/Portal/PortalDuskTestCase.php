<?php

namespace FluxErp\Tests\Browser\Portal;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\DuskTestCase;

class PortalDuskTestCase extends DuskTestCase
{
    protected static string $guard = 'address';

    public Client $dbClient;

    public function createLoginUser(): void
    {
        $this->dbClient = Client::factory()->create();

        Language::factory()->create();
        // ensure a language with language code 'en' exists
        if (Language::query()->where('language_code', 'en')->doesntExist()) {
            Language::factory()->create(['language_code' => 'en']);
        }
        $language = Language::query()->where('language_code', 'en')->first();

        $currency = Currency::factory()->create();

        $country = Country::factory()->create([
            'language_id' => $language->id,
            'currency_id' => $currency->id,
            'is_default' => true,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create([
                'is_active' => true,
                'is_default' => true,
                'is_sales' => true,
                'is_purchase' => true,
            ]);

        $priceList = PriceList::factory()->create([
            'is_default' => true,
        ]);

        $contact = Contact::factory()->create([
            'price_list_id' => $priceList->id,
            'client_id' => $this->dbClient->getKey(),
            'payment_type_id' => $paymentType->id,
        ]);

        $this->user = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
            'language_id' => $language->id,
            'country_id' => $country->id,
            'can_login' => true,
            'password' => $this->password,
            'is_main_address' => true,
        ]);
    }

    public function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('auth.defaults.guard', 'address');
    }

    protected function baseUrl(): string
    {
        return config('flux.portal_domain') . ':' . static::getBaseServePort();
    }
}

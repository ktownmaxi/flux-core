<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PriceList;
use FluxErp\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class PortalSetup extends TestCase
{
    protected Model $dbClient;

    protected string $defaultLanguageCode;

    protected string $portalDomain;

    protected Address $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClient = Client::factory()->create();
        $language = Language::query()->where('language_code', config('app.locale'))->first();
        if (! $language) {
            $language = Language::factory()->create(['language_code' => config('app.locale')]);
        }

        $this->defaultLanguageCode = $language->language_code;

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->user = Address::factory()->create([
            'contact_id' => $this->contact->id,
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
        ]);

        PriceList::factory()->create(['is_default' => true]);

        Currency::factory()->create(['is_default' => true]);

        $this->portalDomain = config('flux.portal_domain');
    }
}

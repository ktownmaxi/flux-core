<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsCurrenciesTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_currencies_page()
    {
        $this->user->givePermissionTo(Permission::findByName('settings.currencies.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/currencies')
            ->assertStatus(200);
    }

    public function test_settings_currencies_no_user()
    {
        $this->get('/settings/currencies')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_currencies_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/settings/currencies')
            ->assertStatus(403);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsClientsCustomerPortalTest extends BaseSetup
{
    public function test_settings_clients_customer_portal_client_not_found(): void
    {
        $this->dbClient->delete();

        $this->user->givePermissionTo(
            Permission::findOrCreate('settings.clients.{client}.customer-portal.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get(
            '/settings/clients/' . $this->dbClient->getKey() . '/customer-portal'
        )
            ->assertStatus(404);
    }

    public function test_settings_clients_customer_portal_no_user(): void
    {
        $this->get('/settings/clients/' . $this->dbClient->getKey() . '/customer-portal')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_clients_customer_portal_page(): void
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('settings.clients.{client}.customer-portal.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get(
            '/settings/clients/' . $this->dbClient->getKey() . '/customer-portal'
        )
            ->assertStatus(200);
    }

    public function test_settings_clients_customer_portal_without_permission(): void
    {
        Permission::findOrCreate('settings.clients.{client}.customer-portal.get', 'web');

        $this->actingAs($this->user, 'web')->get(
            '/settings/clients/' . $this->dbClient->getKey() . '/customer-portal'
        )
            ->assertStatus(403);
    }
}

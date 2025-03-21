<?php

namespace FluxErp\Tests\Livewire\Auth;

use FluxErp\Livewire\Auth\Login;
use FluxErp\Mail\MagicLoginLink;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

class LoginTest extends BaseSetup
{
    protected function setUp(): void
    {
        // logout user
        parent::setUp();

        app('auth')->logout();
    }

    public function test_login_link(): void
    {
        Mail::fake();

        Livewire::test(Login::class)
            ->set('email', $this->user->email)
            ->call('login')
            ->assertNoRedirect()
            ->assertDispatched('tallstackui:toast');

        $this->assertGuest();

        Mail::assertQueued(MagicLoginLink::class);
    }

    public function test_login_successful(): void
    {
        Livewire::test(Login::class)
            ->set('email', $this->user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_wrong_password(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'noexistingmail@example.com')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertNoRedirect()
            ->assertDispatched('tallstackui:toast');

        $this->assertGuest();
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(Login::class)
            ->assertStatus(200);
    }
}

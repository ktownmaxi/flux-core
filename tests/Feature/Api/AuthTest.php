<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\InterfaceUser;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class AuthTest extends TestCase
{
    private Model $interfaceUser;

    private string $interfaceUserPassword;

    private string $password;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $language = Language::factory()->create();

        $this->password = 'password';

        $this->user = new User();
        $this->user->language_id = $language->id;
        $this->user->email = 'TestUser';
        $this->user->firstname = 'TestUserFirstname';
        $this->user->lastname = 'TestUserLastname';
        $this->user->password = $this->password;
        $this->user->is_active = true;
        $this->user->save();

        $this->interfaceUserPassword = Str::random();

        $this->interfaceUser = new InterfaceUser();
        $this->interfaceUser->name = Str::random();
        $this->interfaceUser->password = $this->interfaceUserPassword;
        $this->interfaceUser->is_active = true;
        $this->interfaceUser->save();
    }

    public function test_authenticate(): void
    {
        $response = $this->post('/api/auth/token', [
            'email' => $this->user->email,
            'password' => $this->password,
        ]);
        $response->assertStatus(200);

        $token = json_decode($response->getContent())->access_token;
        $this->assertNotEmpty($token);
    }

    public function test_authenticate_interface_user(): void
    {
        $response = $this->post('/api/auth/token', [
            'email' => $this->interfaceUser->name,
            'password' => $this->interfaceUserPassword,
        ]);

        $response->assertStatus(200);
        $token = json_decode($response->getContent())->access_token;
        $this->assertNotEmpty($token);
    }

    public function test_authenticate_interface_user_invalid_credentials(): void
    {
        $response = $this->post('/api/auth/token', [
            'email' => $this->interfaceUser->name,
            'password' => Str::random() . $this->interfaceUserPassword,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticate_interface_user_user_inactive(): void
    {
        $this->interfaceUser->is_active = false;
        $this->interfaceUser->save();

        $response = $this->post('/api/auth/token', [
            'email' => $this->interfaceUser->name,
            'password' => $this->interfaceUserPassword,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticate_invalid_credentials(): void
    {
        $response = $this->post('/api/auth/token', [
            'email' => $this->user->email,
            'password' => Str::random(),
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticate_validation_fails(): void
    {
        $response = $this->post('/api/auth/token', [
            'email' => 42,
            'password' => 42,
        ]);

        $response->assertStatus(422);
    }

    public function test_validate_token(): void
    {
        $permission = Permission::findOrCreate('api.auth.token.validate.get');
        $this->user->givePermissionTo($permission);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/auth/token/validate');
        $response->assertStatus(200);
    }
}

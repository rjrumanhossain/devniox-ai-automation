<?php

namespace Tests\Feature;

use App\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PortalAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_login_and_receive_super_admin_portal(): void
    {
        $this->seed();

        $this->postJson('/portal-api/login', [
            'email' => 'admin@admin.com',
            'password' => '12345678',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'super_admin');

        $this->getJson('/portal-api/overview')
            ->assertOk()
            ->assertJsonPath('role', 'super_admin')
            ->assertJsonPath('clients.0.name', 'BoneekBD');
    }

    public function test_customer_can_login_and_receive_customer_portal(): void
    {
        $this->seed();

        $this->postJson('/portal-api/login', [
            'email' => 'rumank@gmail.com',
            'password' => '12345678',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'customer');

        $this->getJson('/portal-api/overview')
            ->assertOk()
            ->assertJsonPath('role', 'customer')
            ->assertJsonPath('business.name', 'BoneekBD')
            ->assertJsonPath('business.api_key_prefix', 'dnx_live_boneekbd');
    }

    public function test_invalid_login_is_rejected(): void
    {
        $this->seed();

        $this->postJson('/portal-api/login', [
            'email' => 'admin@admin.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_customer_registration_creates_tenant_and_logs_user_in(): void
    {
        config(['tenancy.domain' => 'example.test']);
        $this->seed();

        $this->postJson('/portal-api/register', [
            'name' => 'John Owner',
            'email' => 'john@example.test',
            'password' => '12345678',
            'business_name' => 'John Fashion',
            'username' => 'johnfashion',
            'plan_slug' => 'starter',
        ])
            ->assertCreated()
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('business.username', 'johnfashion')
            ->assertJsonPath('business.tenant_url', 'https://johnfashion.example.test')
            ->assertJsonStructure(['api_key']);

        $this->assertDatabaseHas('businesses', [
            'slug' => 'johnfashion',
            'tenant_domain' => 'johnfashion.example.test',
        ]);

        $this->getJson('/portal-api/overview')
            ->assertOk()
            ->assertJsonPath('business.username', 'johnfashion');
    }

    public function test_registration_rejects_reserved_and_duplicate_usernames(): void
    {
        $this->seed();

        $payload = [
            'name' => 'Bad Owner',
            'email' => 'bad@example.test',
            'password' => '12345678',
            'business_name' => 'Bad Business',
            'username' => 'admin',
        ];

        $this->postJson('/portal-api/register', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');

        $this->postJson('/portal-api/register', [
            ...$payload,
            'email' => 'dupe@example.test',
            'username' => 'boneekbd',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    public function test_username_availability_endpoint_returns_tenant_url(): void
    {
        config(['tenancy.domain' => 'example.test']);
        $this->seed();

        $this->getJson('/portal-api/username-availability?username=new-shop')
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('tenant_url', 'https://new-shop.example.test');

        $this->getJson('/portal-api/username-availability?username=admin')
            ->assertOk()
            ->assertJsonPath('available', false);
    }

    public function test_tenant_subdomain_resolves_current_tenant(): void
    {
        config(['tenancy.domain' => 'example.test']);
        $this->seed();

        $middleware = new ResolveTenant;
        $request = Request::create('https://boneekbd.example.test');

        $middleware->handle($request, fn () => response('ok'));

        $this->assertTrue(app()->bound('currentTenant'));
        $this->assertSame('boneekbd', app('currentTenant')->slug);

        app()->forgetInstance('currentTenant');
        $this->expectException(NotFoundHttpException::class);

        $middleware->handle(Request::create('https://missing.example.test'), fn () => response('ok'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_role_and_ignores_injected_role(): void
    {
        $res = $this->post('/dang-ky', [
            'name' => 'An', 'email' => 'an@example.com', 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123',
            'role' => 'admin', 'is_active' => 0,
        ]);
        $res->assertRedirect(route('home'));
        $u = User::where('email', 'an@example.com')->first();
        $this->assertNotNull($u);
        $this->assertTrue($u->hasRole('user'));
        $this->assertFalse($u->isAdmin());
        $this->assertTrue((bool) $u->fresh()->is_active);
        $this->assertAuthenticatedAs($u);
    }

    public function test_register_validates_input(): void
    {
        $this->post('/dang-ky', ['name' => '', 'email' => 'x', 'password' => '123', 'password_confirmation' => '1'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_login_success_and_failure(): void
    {
        $u = User::factory()->create(['email' => 'a@example.com']); // mật khẩu "password"
        $this->post('/dang-nhap', ['email' => 'a@example.com', 'password' => 'sai'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/dang-nhap', ['email' => 'a@example.com', 'password' => 'password'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($u);
    }

    public function test_locked_user_cannot_login_and_is_logged_out_mid_session(): void
    {
        $u = User::factory()->locked()->create(['email' => 'l@example.com']);
        $this->post('/dang-nhap', ['email' => 'l@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($u)->get('/tai-lieu-cua-toi')->assertRedirect(route('login'));
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'r@example.com']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/dang-nhap', ['email' => 'r@example.com', 'password' => 'sai']);
        }
        $this->post('/dang-nhap', ['email' => 'r@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_area_requires_admin_role_on_server(): void
    {
        $this->get('/quan-tri')->assertRedirect(route('login'));
        $user = User::factory()->create();
        foreach (['/quan-tri', '/quan-tri/nguoi-dung', '/quan-tri/kiem-duyet', '/quan-tri/cau-hinh', '/quan-tri/giam-sat'] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
        $this->actingAs($user)->post('/quan-tri/nguoi-dung/' . $user->id . '/vai-tro', ['role' => 'admin'])->assertForbidden();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/quan-tri')->assertOk();
    }

    public function test_guest_pages_and_protected_pages(): void
    {
        foreach (['/', '/tai-lieu', '/tim-kiem', '/dang-nhap', '/dang-ky'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/tai-len', '/tai-lieu-cua-toi', '/yeu-thich', '/tro-ly-ai', '/thong-bao', '/ho-so'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}

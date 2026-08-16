<?php

namespace Tests\Feature;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class JwtCookieVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Roles::query()->delete();
        Roles::create(['id' => 1, 'name' => 'Admin', 'guard_name' => 'admin']);
        Roles::create(['id' => 2, 'name' => 'User', 'guard_name' => 'user']);
    }

    protected function extractCookieValue($response, string $name): ?string
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie->getValue();
            }
        }

        return null;
    }

    protected function resetAuthState(): void
    {
        Auth::forgetGuards();
        app()->forgetInstance('tymon.jwt');
        app()->forgetInstance('tymon.jwt.auth');
    }

    public function test_login_before_verification_returns_403_and_sends_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Email belum diverifikasi. Link verifikasi sudah dikirim ke email kamu.');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_link_sets_email_and_session_fields(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
            'verification_expires_at' => null,
        ]);

        $expires = now()->addHour()->timestamp;
        $hash = sha1($user->getEmailForVerification());
        $signature = hash_hmac('sha256', "verification.verify|{$user->id}|{$hash}|{$expires}", config('app.key'));

        $response = $this->getJson('/api/auth/email/verify/' . $user->id . '/' . $hash . '?expires=' . $expires . '&signature=' . $signature);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Email berhasil diverifikasi');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->verification_expires_at);
    }

    public function test_login_after_verification_sets_cookie_tokens(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'verification_expires_at' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertCookie('access_token')
            ->assertCookie('XSRF-TOKEN');

        $this->assertNotNull($response->headers->getCookies()[0]->getValue());
    }

    public function test_me_endpoint_works_with_cookie_token(): void
     {
     $user = User::factory()->create([
          'password' => Hash::make('password123'),
          'email_verified_at' => now(),
          'verification_expires_at' => now()->addDay(),
     ]);

     $login = $this->postJson('/api/login', [
          'email' => $user->email,
          'password' => 'password123',
     ]);

     $token = $this->extractCookieValue($login, 'access_token');
     $csrf = $this->extractCookieValue($login, 'XSRF-TOKEN');

     $this->resetAuthState();

     $this->jsonWithCookies('GET', '/api/me', ['access_token' => $token], [], ['X-XSRF-TOKEN' => $csrf])
          ->assertStatus(200)
          ->assertJsonPath('data.email', $user->email);
     }
    
    
     public function test_missing_csrf_header_returns_419_for_mutation_route(): void
     {
     $user = User::factory()->create([
          'password' => Hash::make('password123'),
          'email_verified_at' => now(),
          'verification_expires_at' => now()->addDay(),
     ]);

     $login = $this->postJson('/api/login', [
          'email' => $user->email,
          'password' => 'password123',
     ]);

     $token = $this->extractCookieValue($login, 'access_token');

     $this->resetAuthState();

     $this->jsonWithCookies('POST', '/api/logout', ['access_token' => $token])
          ->assertStatus(419);
     }


    public function test_tampered_jwt_returns_401(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'verification_expires_at' => now()->addDay(),
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $token = $this->extractCookieValue($login, 'access_token');
        $tampered = $token . 'bad';

        $this->resetAuthState();

        $this->withHeader('Authorization', 'Bearer ' . $tampered)
            ->getJson('/api/me')
            ->assertStatus(401);
    }

     public function test_refresh_returns_403_when_verification_session_is_expired_and_200_when_valid(): void
     {
     $user = User::factory()->create([
          'password' => Hash::make('password123'),
          'email_verified_at' => now(),
          'verification_expires_at' => now()->addDay(),
     ]);

     $login = $this->postJson('/api/login', [
          'email' => $user->email,
          'password' => 'password123',
     ]);

     $token = $this->extractCookieValue($login, 'access_token');
     $csrf = $this->extractCookieValue($login, 'XSRF-TOKEN');

     $user->update(['verification_expires_at' => now()->subDay()]);
     $this->resetAuthState();

     $this->jsonWithCookies('POST','/api/refresh',['access_token' => $token, 'XSRF-TOKEN' => $csrf],[],['X-XSRF-TOKEN' => $csrf])->assertStatus(403);

     $user->update(['verification_expires_at' => now()->addDay()]);
     $this->resetAuthState();

     $this->jsonWithCookies('POST', '/api/refresh', ['access_token' => $token, 'XSRF-TOKEN' => $csrf], [], ['X-XSRF-TOKEN' => $csrf])
          ->assertStatus(200)
          ->assertJsonPath('success', true);
     }

     public function test_logout_then_login_requires_verification_again(): void
     {
     $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'verification_expires_at' => now()->addDay(),
    ]);

     $login = $this->postJson('/api/login', [
          'email' => $user->email,
          'password' => 'password123',
     ]);

     $token = $this->extractCookieValue($login, 'access_token');
     $csrf = $this->extractCookieValue($login, 'XSRF-TOKEN');

     $this->resetAuthState();

     $this->jsonWithCookies(
        'POST',
        '/api/logout',
        ['access_token' => $token, 'XSRF-TOKEN' => $csrf],
        [],
        ['X-XSRF-TOKEN' => $csrf]
    )->assertStatus(200);

     $this->postJson('/api/login', [
          'email' => $user->email,
          'password' => 'password123',
     ])->assertStatus(403)
        ->assertJsonPath('message', 'Sesi verifikasi kamu sudah kedaluwarsa. Link verifikasi baru sudah dikirim ke email kamu.');
     }

    public function test_idle_longer_than_one_day_triggers_verified_session_403(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'verification_expires_at' => now()->subDay(),
        ]);

        $token = auth()->guard('api')->login($user);

        $this->resetAuthState();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users')
            ->assertStatus(403);
    }

    public function test_rate_limit_for_resend_verification_after_burst(): void
    {
        $user = User::factory()->create([
            'email' => 'limit@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->postJson('/api/auth/email/resend', ['email' => $user->email])
            ->assertStatus(200);

        $this->postJson('/api/auth/email/resend', ['email' => $user->email])
            ->assertStatus(429);
    }
}
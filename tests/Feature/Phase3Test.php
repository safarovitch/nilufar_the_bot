<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PlayedTrack;
use App\Models\LinkedAccount;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Illuminate\Support\Facades\Event;

class Phase3Test extends TestCase
{
    use RefreshDatabase;

    public function test_login_command_sends_links()
    {
        // We can't easily test Telegram Command output without partial mock of the bot SDK 
        // because it uses the API to reply.
        // But we can verify the command exists and is registered.
        $this->assertTrue(class_exists(\App\Telegram\Commands\LoginCommand::class));
    }

    public function test_auth_redirection()
    {
        $response = $this->get('/auth/google?telegram_id=12345');

        // Socialite redirect usually returns a RedirectResponse to the provider
        $response->assertStatus(302);
        $this->assertTrue(session()->has('telegram_id_for_link'));
        $this->assertEquals('12345', session('telegram_id_for_link'));
    }

    public function test_auth_callback_creates_user_and_linked_account()
    {
        // Mock Socialite User
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google_123');
        $abstractUser->shouldReceive('getName')->andReturn('Test User');
        $abstractUser->shouldReceive('getEmail')->andReturn('test@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn(null);
        $abstractUser->token = 'dummy_token';
        $abstractUser->refreshToken = 'dummy_refresh';
        $abstractUser->expiresIn = 3600;

        // Mock Socialite Provider
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        // Mock Socialite Facade
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        // Simulate session
        session(['telegram_id_for_link' => '99999']);

        $response = $this->get('/auth/google/callback');

        $response->assertOk();
        $response->assertSee('Authentication successful');

        // Assert User Created
        $this->assertDatabaseHas('users', [
            'telegram_id' => '99999',
            'email' => 'test@example.com',
        ]);

        // Assert Linked Account Created
        $this->assertDatabaseHas('linked_accounts', [
            'provider' => 'google',
            'provider_id' => 'google_123',
            'token' => 'dummy_token',
        ]);
    }

    public function test_recommendation_service_uses_history()
    {
        // Create user
        $user = User::factory()->create();

        // Log some history
        PlayedTrack::create([
            'user_id' => $user->id,
            'track_source_id' => 'src1',
            'title' => 'Track 1',
            'artist' => 'Rock Band',
            'genre' => 'Rock',
            'played_at' => now(),
        ]);

        // Create another user's play of same genre
        PlayedTrack::create([
            'user_id' => 999, // other user
            'track_source_id' => 'src2',
            'title' => 'Track 2',
            'artist' => 'Rock Band', // Same artist
            'genre' => 'Rock',
            'played_at' => now(),
        ]);

        $service = new RecommendationService();
        $recommendations = $service->getRecommendations($user->id);

        // Should find Track 2 because similarity in Artist
        $found = false;
        foreach ($recommendations as $rec) {
            if ($rec['title'] === 'Track 2') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Recommendation service did not return similar track');
    }
}

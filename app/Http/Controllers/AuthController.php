<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\LinkedAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider, Request $request)
    {
        // We need to pass the telegram_id to link the account.
        // We can pass it via state or query param if supported/persisted.
        // Socialite supports ->with(['state' => ...]) but it handles state automatically.
        // Better: user clicks link /auth/google?telegram_id=123
        // We store 123 in session or pass it via state.

        $telegramId = $request->query('telegram_id');
        if ($telegramId) {
            session(['telegram_id_for_link' => $telegramId]);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param string $provider
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Authentication failed.');
        }

        $telegramId = session('telegram_id_for_link');

        // Find or create user. 
        // If we have telegram_id, we try to find user by it.
        // Or we just find by email if available.

        $user = null;

        if ($telegramId) {
            $user = User::where('telegram_id', $telegramId)->first();

            if (!$user) {
                // Create new user if not exists
                $user = User::create([
                    'name' => $socialUser->getName() ?? 'Telegram User',
                    'email' => $socialUser->getEmail() ?? $telegramId . '@telegram.bot', // Placeholder if no email
                    'password' => bcrypt(Str::random(16)),
                    'telegram_id' => $telegramId,
                ]);
            }
        } else {
            // Fallback: login regular web user or create by email
            $user = User::where('email', $socialUser->getEmail())->first();
            if (!$user && $socialUser->getEmail()) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => bcrypt(Str::random(16)),
                ]);
            }
        }

        if ($user) {
            // Update or Create Linked Account
            LinkedAccount::updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ],
                [
                    'user_id' => $user->id,
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken, // might be null
                    'expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
                ]
            );

            Auth::login($user);

            // Optionally trigger history fetch here (async)
            // \App\Jobs\FetchHistoryJob::dispatch($user, $provider);

            return response('Authentication successful! You can return to Telegram.');
        }

        return response('Unable to link account. Please try again from Telegram.');
    }
}

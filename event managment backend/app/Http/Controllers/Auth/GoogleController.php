<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SocialiteRedirectResponse;
use Throwable;

class GoogleController extends Controller
{
    public function redirectToGoogle(): SocialiteRedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();

            $payload = Validator::make([
                'name' => $googleUser->getName() ?: ($email ? Str::before($email, '@') : null),
                'email' => $email,
                'photo' => $googleUser->getAvatar(),
            ], [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email:rfc', 'max:255'],
                'photo' => ['nullable', 'string', 'max:2048'],
            ])->validate();

            $existingUser = User::query()->firstWhere('email', $payload['email']);

            $user = User::query()->updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'photo' => $payload['photo'] ?? null,
                    'provider' => 'google',
                    'email_verified_at' => $existingUser?->email_verified_at ?? now(),
                    'password' => $existingUser?->password ?: Hash::make(Str::random(40)),
                ]
            );

            Auth::login($user, true);
            $request->session()->regenerate();

         return redirect('http://localhost:5173');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors([
                    'google' => 'Unable to log in with Google right now. Please try again.',
                ]);
        }
    }
}
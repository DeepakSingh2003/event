<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function emailLogin(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = Str::lower($data['email']);
        $adminRoles = [
            'admin@example.com' => 'admin',
            'manager@example.com' => 'manager',
        ];

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => Str::before($email, '@'),
                'email' => $email,
                'provider' => 'email',
                'role' => $adminRoles[$email] ?? 'user',
                'password' => Hash::make(Str::random(40)),
            ]);
        } elseif (isset($adminRoles[$email]) && $user->role !== $adminRoles[$email]) {
            $user->forceFill(['role' => $adminRoles[$email]])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        if (! $request->expectsJson()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return response()->json([
            'user' => $user,
            'message' => 'Login successful',
        ]);
    }
}

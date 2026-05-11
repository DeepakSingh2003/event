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

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => Str::before($data['email'], '@'),
                'email' => $data['email'],
                'provider' => 'email',
                'password' => Hash::make(Str::random(40)),
            ]);
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

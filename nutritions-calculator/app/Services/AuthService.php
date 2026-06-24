<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    public function createUser(string $name, string $email, string $password): User
    {
        return User::create([
            'name' => $name,
            'full_name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(string $token, string $email, string $password): string
    {
        return Password::reset(
            ['token' => $token, 'email' => $email, 'password' => $password],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }
}

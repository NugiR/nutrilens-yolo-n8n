<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class AuthApiController extends Controller
{
    use ApiResponsable;

    public function __construct(private AuthService $authService) {}

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return $this->error('Email atau password salah.', 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 'Login berhasil.');
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user  = $this->authService->createUser($request->name, $request->email, $request->password);
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->created([
            'token' => $token,
            'user'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ], 'Registrasi berhasil.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success($this->formatUser($request->user()));
    }

    private function formatUser($user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'full_name'  => $user->full_name,
            'email'      => $user->email,
            'gender'     => $user->gender?->value,
            'height_cm'  => $user->height_cm,
            'weight_kg'  => $user->weight_kg,
            'bmi'        => $user->bmi,
            'photo_path' => $user->photo_path,
        ];
    }

    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = $this->authService->sendPasswordResetLink($request->email);

        return $this->success(null, __($status));
    }
}

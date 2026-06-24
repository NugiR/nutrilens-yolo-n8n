<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function index(Request $request): View
    {
        return view('profile', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profileService->update(
            user: $request->user(),
            validated: $request->validated(),
            photo: $request->file('photo'),
        );

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}

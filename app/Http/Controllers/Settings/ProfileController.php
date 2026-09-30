<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.profile', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request, AccountService $accounts): RedirectResponse
    {
        $accounts->updateProfile(
            $request->user(),
            $request->validated(),
            $request->file('avatar'),
            $request->boolean('remove_avatar'),
        );

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}

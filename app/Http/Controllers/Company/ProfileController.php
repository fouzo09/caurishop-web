<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load('roles');

        return view('company.profile.index', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        if ($user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);

        return redirect()->route('company.profile')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        auth()->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('company.profile')
            ->with('password_success', 'Mot de passe modifié avec succès.');
    }
}

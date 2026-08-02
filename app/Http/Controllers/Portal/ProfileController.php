<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        if ($user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }

        $phone = $data['phone'] ?? null;
        unset($data['phone']);
        $user->update($data);

        if ($phone !== null) {
            $user->customer?->update(['phone' => $phone]);
        }

        return redirect()->route('portal.profile')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        auth()->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('portal.profile')
            ->with('password_success', 'Mot de passe modifié avec succès.');
    }
}

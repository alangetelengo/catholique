<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Traits\LogsErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use LogsErrors;

    public function edit(): View
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            $user->update($request->validated());

            FlashAlert::success('Profil mis à jour avec succès.');

            return redirect()->route('profile.edit');
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour du profil');
            FlashAlert::error('Une erreur est survenue lors de la mise à jour du profil.');

            return back()->withInput();
        }
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! Hash::check($request->string('current_password'), $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Le mot de passe actuel est incorrect.',
                ]);
            }

            $user->update([
                'password' => $request->string('password'),
            ]);

            FlashAlert::success('Mot de passe mis à jour avec succès.');

            return redirect()->route('profile.edit');
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour du mot de passe');
            FlashAlert::error('Une erreur est survenue lors de la mise à jour du mot de passe.');

            return back();
        }
    }
}


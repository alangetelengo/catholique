<?php

namespace App\Http\Controllers;

use App\Models\Paroisse;
use App\Models\Role;
use App\Models\User;
use App\Traits\LogsErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use LogsErrors;

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('application-configuration.index', array_merge(
            $request->query(),
            ['tab' => 'users']
        ));
    }

    public function create(): View
    {
        $user = new User;
        $roles = Role::query()->orderBy('name')->get();
        $paroisses = Paroisse::query()->orderBy('nom')->get();

        return view('users.create', compact('user', 'roles', 'paroisses'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $request->merge([
                'paroisse_id' => $request->filled('paroisse_id') ? (int) $request->input('paroisse_id') : null,
            ]);

            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^[a-zA-Z0-9_.-]+$/',
                    Rule::unique('users', 'username'),
                ],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['nullable', 'exists:roles,name'],
                'paroisse_id' => [
                    'nullable',
                    'integer',
                    'exists:paroisses,id',
                    Rule::requiredIf(fn () => ($request->input('role') ?? '') !== 'super_admin'),
                ],
            ]);

            $username = Str::lower(trim($data['username']));

            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $username,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'paroisse_id' => $data['paroisse_id'] ?? null,
            ]);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            $this->logInfo('Utilisateur créé', ['user_id' => $user->id, 'email' => $user->email]);

            return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors de la création utilisateur');

            return back()->withInput()->with('error', 'Une erreur est survenue lors de la création.');
        }
    }

    public function edit(User $user): View
    {
        $roles = Role::query()->orderBy('name')->get();
        $paroisses = Paroisse::query()->orderBy('nom')->get();

        return view('users.edit', compact('user', 'roles', 'paroisses'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        try {
            $usernameInput = trim((string) $request->input('username', ''));

            $request->merge([
                'paroisse_id' => $request->filled('paroisse_id') ? (int) $request->input('paroisse_id') : null,
                'username' => $usernameInput !== '' ? $usernameInput : null,
            ]);

            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => [
                    'nullable',
                    'string',
                    'max:80',
                    'regex:/^[a-zA-Z0-9_.-]+$/',
                    Rule::unique('users', 'username')->ignore($user->id),
                ],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
                'role' => ['nullable', 'exists:roles,name'],
                'paroisse_id' => [
                    'nullable',
                    'integer',
                    'exists:paroisses,id',
                    Rule::requiredIf(fn () => ($request->input('role') ?? '') !== 'super_admin'),
                ],
            ]);

            $username = filled($data['username'])
                ? Str::lower(trim((string) $data['username']))
                : null;

            $user->update([
                'name' => $data['name'],
                'username' => $username,
                'email' => $data['email'],
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $user->password,
                'paroisse_id' => $data['paroisse_id'] ?? null,
            ]);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            $this->logInfo('Utilisateur mis à jour', ['user_id' => $user->id, 'email' => $user->email]);

            return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour utilisateur', ['user_id' => $user->id]);

            return back()->withInput()->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return back()->with('error', 'Tu ne peux pas supprimer ton propre compte.');
        }

        try {
            $user->delete();
            $this->logInfo('Utilisateur supprimé', ['user_id' => $user->id, 'email' => $user->email]);

            return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors de la suppression utilisateur', ['user_id' => $user->id]);

            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }
}

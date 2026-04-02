<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\LogsErrors;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    use LogsErrors;

    /**
     * @return array<int, Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_roles'),
        ];
    }

    public function index()
    {
        $roles = Role::query()->orderBy('name')->paginate(20);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::query()->orderBy('name')->get();

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'libelle_role' => ['required', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        try {
            $role = Role::create([
                'name' => $data['name'],
                'libelle_role' => $data['libelle_role'],
                'guard_name' => 'web',
            ]);

            if (! empty($data['permissions'])) {
                $role->syncPermissions(Permission::whereIn('id', $data['permissions'])->get());
            }

            FlashAlert::success('Rôle créé avec succès.');

            return redirect()->route('roles.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la création du rôle', $e, ['data' => $data]);
            FlashAlert::error('Une erreur est survenue lors de la création du rôle.');

            return back()->withInput();
        }
    }

    public function edit(Role $role)
    {
        $permissions = Permission::query()->orderBy('name')->get();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'libelle_role' => ['required', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        try {
            $role->update([
                'name' => $data['name'],
                'libelle_role' => $data['libelle_role'],
            ]);

            $role->syncPermissions(
                ! empty($data['permissions'])
                    ? Permission::whereIn('id', $data['permissions'])->get()
                    : []
            );

            FlashAlert::success('Rôle mis à jour avec succès.');

            return redirect()->route('roles.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la mise à jour du rôle', $e, ['role_id' => $role->id, 'data' => $data]);
            FlashAlert::error('Une erreur est survenue lors de la mise à jour du rôle.');

            return back()->withInput();
        }
    }

    public function destroy(Role $role)
    {
        try {
            if ($role->name === 'super_admin') {
                FlashAlert::error('Le rôle super_admin ne peut pas être supprimé.');

                return back();
            }

            $role->delete();

            FlashAlert::success('Rôle supprimé avec succès.');

            return redirect()->route('roles.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la suppression du rôle', $e, ['role_id' => $role->id]);
            FlashAlert::error('Une erreur est survenue lors de la suppression du rôle.');

            return back();
        }
    }
}

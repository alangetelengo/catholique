<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\Permission;
use App\Traits\LogsErrors;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class PermissionController extends Controller implements HasMiddleware
{
    use LogsErrors;

    /**
     * @return array<int, Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_permissions'),
        ];
    }

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('application-configuration.index', array_merge(
            $request->query(),
            ['tab' => 'permissions']
        ));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'libelle_permission' => ['required', 'string', 'max:255'],
        ]);

        try {
            Permission::create([
                'name' => $data['name'],
                'libelle_permission' => $data['libelle_permission'],
                'guard_name' => 'web',
            ]);

            FlashAlert::success('Permission créée avec succès.');

            return redirect()->route('permissions.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la création de la permission', $e, ['data' => $data]);
            FlashAlert::error('Une erreur est survenue lors de la création de la permission.');

            return back()->withInput();
        }
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
            'libelle_permission' => ['required', 'string', 'max:255'],
        ]);

        try {
            $permission->update([
                'name' => $data['name'],
                'libelle_permission' => $data['libelle_permission'],
            ]);

            FlashAlert::success('Permission mise à jour avec succès.');

            return redirect()->route('permissions.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la mise à jour de la permission', $e, ['permission_id' => $permission->id, 'data' => $data]);
            FlashAlert::error('Une erreur est survenue lors de la mise à jour de la permission.');

            return back()->withInput();
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            $permission->delete();

            FlashAlert::success('Permission supprimée avec succès.');

            return redirect()->route('permissions.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la suppression de la permission', $e, ['permission_id' => $permission->id]);
            FlashAlert::error('Une erreur est survenue lors de la suppression de la permission.');

            return back();
        }
    }
}

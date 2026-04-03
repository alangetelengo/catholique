<?php

namespace App\Http\Controllers;

use App\Models\Paroisse;
use App\Models\Permission;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Models\Role;
use App\Models\User;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ApplicationConfigurationController extends Controller
{
    /**
     * @return array<string, string>
     */
    private function visibleTabs(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $tabs = [];

        if ($user->can('manage_paroisses') || $user->can('view_configuration')) {
            $tabs['paroisses'] = 'Paroisses';
        }
        if ($user->can('manage_users')) {
            $tabs['users'] = 'Utilisateurs';
        }
        if ($user->can('manage_roles')) {
            $tabs['roles'] = 'Rôles';
        }
        if ($user->can('manage_permissions')) {
            $tabs['permissions'] = 'Permissions';
        }

        $tabs['revenue-categories'] = 'Catégories recettes';
        $tabs['revenue-types'] = 'Types recettes';

        if ($user->can('view_configuration')) {
            $tabs['appearance'] = 'Paramètres paroisse';
        }

        return $tabs;
    }

    public function index(Request $request): View|Response
    {
        $user = $request->user();
        $tabs = $this->visibleTabs($user);
        abort_if(empty($tabs), 403);

        $tab = (string) $request->query('tab', array_key_first($tabs));
        if (! array_key_exists($tab, $tabs)) {
            $tab = (string) array_key_first($tabs);
        }

        $panel = $this->renderPanel($request, $tab);

        if ($request->ajax() && $request->boolean('panel_only')) {
            return response($panel->render());
        }

        return view('application-configuration.index', [
            'tabs' => $tabs,
            'activeTab' => $tab,
            'panelHtml' => $panel->render(),
        ]);
    }

    private function renderPanel(Request $request, string $tab): View
    {
        return match ($tab) {
            'paroisses' => $this->panelParoisses($request),
            'users' => $this->panelUsers($request),
            'roles' => $this->panelRoles($request),
            'permissions' => $this->panelPermissions($request),
            'revenue-categories' => $this->panelRevenueCategories($request),
            'revenue-types' => $this->panelRevenueTypes($request),
            'appearance' => $this->panelAppearance($request),
            default => abort(404),
        };
    }

    private function ensureTabAccess(Request $request, string $tab): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        $allowed = match ($tab) {
            'paroisses' => $user->can('manage_paroisses') || $user->can('view_configuration'),
            'users' => $user->can('manage_users'),
            'roles' => $user->can('manage_roles'),
            'permissions' => $user->can('manage_permissions'),
            'revenue-categories', 'revenue-types' => true,
            'appearance' => $user->can('view_configuration'),
            default => false,
        };

        abort_unless($allowed, 403);
    }

    private function panelParoisses(Request $request): View
    {
        $this->ensureTabAccess($request, 'paroisses');

        $perPage = PaginationPerPage::resolve($request);

        $query = Paroisse::query()
            ->with('curé')
            ->orderBy('nom');

        if (! $request->user()?->hasRole('super_admin')) {
            if ($request->user()?->paroisse_id) {
                $query->where('id', $request->user()->paroisse_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            if ($request->filled('q')) {
                $s = '%'.addcslashes(mb_strtolower($request->string('q')->value()), '%_\\').'%';
                $query->where(function ($q) use ($s): void {
                    $q->whereRaw('LOWER(nom) LIKE ?', [$s])
                        ->orWhereRaw('LOWER(COALESCE(ville, "")) LIKE ?', [$s])
                        ->orWhereRaw('LOWER(COALESCE(code_paroisse, "")) LIKE ?', [$s]);
                });
            }
            if ($request->filled('actif')) {
                $query->where('actif', $request->boolean('actif'));
            }
        }

        $paroisses = $query->paginate($perPage)->withQueryString();
        $canManage = $request->user()?->hasRole('super_admin') ?? false;

        return view('application-configuration.panels.paroisses', compact('paroisses', 'canManage'));
    }

    private function panelUsers(Request $request): View
    {
        $this->ensureTabAccess($request, 'users');

        $perPage = PaginationPerPage::resolve($request);

        $query = User::query()->with(['roles', 'paroisse'])->orderBy('name');

        if ($request->filled('q')) {
            $search = mb_strtolower($request->string('q')->value());
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            });
        }

        $users = $query->paginate($perPage)->withQueryString();

        return view('application-configuration.panels.users', compact('users'));
    }

    private function panelRoles(Request $request): View
    {
        $this->ensureTabAccess($request, 'roles');

        $perPage = PaginationPerPage::resolve($request);
        $roles = Role::query()->orderBy('name')->paginate($perPage)->withQueryString();

        return view('application-configuration.panels.roles', compact('roles'));
    }

    private function panelPermissions(Request $request): View
    {
        $this->ensureTabAccess($request, 'permissions');

        $perPage = PaginationPerPage::resolve($request);
        $permissions = Permission::query()->orderBy('name')->paginate($perPage)->withQueryString();

        return view('application-configuration.panels.permissions', compact('permissions'));
    }

    private function panelRevenueCategories(Request $request): View
    {
        $this->ensureTabAccess($request, 'revenue-categories');

        $perPage = PaginationPerPage::resolve($request);
        $categories = RevenueCategory::query()
            ->orderBy('ordre')
            ->orderBy('nom')
            ->paginate($perPage)
            ->withQueryString();

        return view('application-configuration.panels.revenue-categories', compact('categories'));
    }

    private function panelRevenueTypes(Request $request): View
    {
        $this->ensureTabAccess($request, 'revenue-types');

        $perPage = PaginationPerPage::resolve($request);

        $query = RevenueType::query()->with('category')->orderBy('ordre')->orderBy('nom');

        if ($request->filled('revenue_category_id')) {
            $query->where('revenue_category_id', $request->integer('revenue_category_id'));
        }

        $types = $query->paginate($perPage)->withQueryString();
        $categories = RevenueCategory::query()->orderBy('ordre')->orderBy('nom')->get();

        return view('application-configuration.panels.revenue-types', compact('types', 'categories'));
    }

    private function panelAppearance(Request $request): View
    {
        $this->ensureTabAccess($request, 'appearance');

        return view('application-configuration.panels.appearance');
    }
}

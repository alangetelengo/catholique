<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\Member;
use App\Models\Paroisse;
use App\Support\PaginationPerPage;
use App\Traits\LogsErrors;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller implements HasMiddleware
{
    use LogsErrors;

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_events', only: ['index', 'show']),
            new Middleware('permission:create_events', only: ['create', 'store']),
            new Middleware('permission:edit_events', only: ['edit', 'update']),
            new Middleware('permission:delete_events', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Event::query()->with(['paroisse', 'celebrePar']);

            if (Auth::check() && ! Auth::user()->hasRole('super_admin')) {
                if (Auth::user()->paroisse_id) {
                    $query->where('paroisse_id', Auth::user()->paroisse_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } elseif ($request->filled('paroisse_id')) {
                $query->where('paroisse_id', $request->input('paroisse_id'));
            }

            if ($type = $request->input('type')) {
                $query->where('type', $type);
            }

            if ($dateFrom = $request->input('date_from')) {
                $query->whereDate('date_evenement', '>=', $dateFrom);
            }

            if ($dateTo = $request->input('date_to')) {
                $query->whereDate('date_evenement', '<=', $dateTo);
            }

            $events = $query->orderByDesc('date_evenement')->orderByDesc('heure_evenement')->paginate(PaginationPerPage::resolve($request))->withQueryString();

            $paroisses = Auth::check() && Auth::user()->hasRole('super_admin')
                ? Paroisse::query()->orderBy('nom')->get()
                : collect();

            return view('events.index', compact('events', 'paroisses'));
        } catch (Exception $e) {
            $this->logError('Erreur lors de la récupération des événements', $e);
            FlashAlert::error('Une erreur est survenue lors de la récupération des événements.');

            return redirect()->route('home');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $paroisses = Auth::check() && Auth::user()->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : collect();

        $celebrants = Member::query()
            ->when(
                Auth::check() && ! Auth::user()->hasRole('super_admin') && Auth::user()->paroisse_id,
                fn ($q) => $q->where('paroisse_id', Auth::user()->paroisse_id)
            )
            ->where(function ($q): void {
                $q->where('notes', 'like', '%curé%')
                    ->orWhere('notes', 'like', '%abbé%')
                    ->orWhere('notes', 'like', '%père%')
                    ->orWhere('notes', 'like', '%pere%');
            })
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();

        return view('events.create', compact('paroisses', 'celebrants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        try {
            $validated = $request->validated();

            if (Auth::check() && ! Auth::user()->hasRole('super_admin')) {
                $validated['paroisse_id'] = Auth::user()->paroisse_id;
            }

            $event = Event::create($validated);

            $this->logInfo('Événement créé', ['event_id' => $event->id]);
            FlashAlert::success('L\'événement a été créé avec succès.');

            return redirect()->route('events.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la création de l\'événement', $e, ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de la création de l\'événement.');

            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $this->ensureEventAccessible($event);

        $event->load(['paroisse', 'celebrePar', 'participants']);

        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $this->ensureEventAccessible($event);

        $paroisses = Auth::check() && Auth::user()->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : collect();

        $celebrants = Member::query()
            ->when(
                Auth::check() && ! Auth::user()->hasRole('super_admin') && Auth::user()->paroisse_id,
                fn ($q) => $q->where('paroisse_id', Auth::user()->paroisse_id)
            )
            ->where(function ($q): void {
                $q->where('notes', 'like', '%curé%')
                    ->orWhere('notes', 'like', '%abbé%')
                    ->orWhere('notes', 'like', '%père%')
                    ->orWhere('notes', 'like', '%pere%');
            })
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();

        return view('events.edit', compact('event', 'paroisses', 'celebrants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        try {
            $this->ensureEventAccessible($event);

            $validated = $request->validated();

            if (Auth::check() && ! Auth::user()->hasRole('super_admin')) {
                unset($validated['paroisse_id']);
            }

            $event->update($validated);

            $this->logInfo('Événement mis à jour', ['event_id' => $event->id]);
            FlashAlert::success('L\'événement a été mis à jour avec succès.');

            return redirect()->route('events.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la mise à jour de l\'événement', $e, [
                'event_id' => $event->id,
                'data' => $request->all(),
            ]);
            FlashAlert::error('Une erreur est survenue lors de la mise à jour de l\'événement.');

            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        try {
            $this->ensureEventAccessible($event);

            $event->delete();

            $this->logInfo('Événement supprimé', ['event_id' => $event->id]);
            FlashAlert::success('L\'événement a été supprimé avec succès.');

            return redirect()->route('events.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la suppression de l\'événement', $e, ['event_id' => $event->id]);
            FlashAlert::error('Une erreur est survenue lors de la suppression de l\'événement.');

            return back();
        }
    }

    private function ensureEventAccessible(Event $event): void
    {
        if (! Auth::check()) {
            abort(403);
        }

        if (Auth::user()->hasRole('super_admin')) {
            return;
        }

        if (Auth::user()->paroisse_id && $event->paroisse_id === Auth::user()->paroisse_id) {
            return;
        }

        abort(404);
    }
}

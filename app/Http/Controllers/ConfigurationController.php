<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\Configuration;
use App\Traits\LogsErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ConfigurationController extends Controller
{
    use LogsErrors;

    /**
     * Affiche la liste des configurations
     */
    public function index()
    {
        try {
            $paroisseId = auth()->check() ? (auth()->user()->paroisse_id ?? null) : null;

            $configurations = Configuration::where('paroisse_id', $paroisseId)
                ->where('actif', true)
                ->orderBy('cle')
                ->get()
                ->groupBy(function ($config) {
                    // Grouper par catégorie (déterminée par le préfixe de la clé)
                    if (str_starts_with($config->cle, 'couleur_')) {
                        return 'couleurs';
                    }
                    if (str_starts_with($config->cle, 'nom_') || str_starts_with($config->cle, 'logo_')) {
                        return 'identite';
                    }
                    if (in_array($config->cle, ['monnaie', 'format_date', 'format_heure', 'langue'])) {
                        return 'general';
                    }
                    return 'autres';
                });

            return view('configurations.index', compact('configurations', 'paroisseId'));
        } catch (\Exception $e) {
            $this->logError('Erreur lors de la récupération des configurations', $e);
            FlashAlert::error('Une erreur est survenue lors de la récupération des configurations.');
            return redirect()->route('home');
        }
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return view('configurations.create');
    }

    /**
     * Enregistre une nouvelle configuration
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cle' => 'required|string|max:255',
                'valeur' => 'required',
                'type' => 'required|in:string,integer,boolean,json,float',
                'description' => 'nullable|string',
            ]);

            $paroisseId = auth()->check() ? (auth()->user()->paroisse_id ?? null) : null;

            Configuration::setValue(
                $paroisseId,
                $validated['cle'],
                $validated['valeur'],
                $validated['type'],
                $validated['description'] ?? null
            );

            // Vider le cache
            Cache::forget("config_{$paroisseId}_{$validated['cle']}");

            $this->logInfo("Configuration créée : {$validated['cle']}");
            FlashAlert::success("La configuration a été créée avec succès.");

            return redirect()->route('configurations.index');
        } catch (\Exception $e) {
            $this->logError('Erreur lors de la création de la configuration', $e, ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de la création de la configuration.');
            return back()->withInput();
        }
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Configuration $configuration)
    {
        return view('configurations.edit', compact('configuration'));
    }

    /**
     * Met à jour une configuration
     */
    public function update(Request $request, Configuration $configuration)
    {
        try {
            $validated = $request->validate([
                'valeur' => 'required',
                'type' => 'required|in:string,integer,boolean,json,float',
                'description' => 'nullable|string',
            ]);

            $configuration->update([
                'valeur' => match ($validated['type']) {
                    'json' => json_encode($validated['valeur']),
                    'boolean' => $validated['valeur'] ? '1' : '0',
                    default => (string) $validated['valeur'],
                },
                'type' => $validated['type'],
                'description' => $validated['description'] ?? $configuration->description,
            ]);

            // Vider le cache
            Cache::forget("config_{$configuration->paroisse_id}_{$configuration->cle}");

            $this->logInfo("Configuration mise à jour : {$configuration->cle}");
            FlashAlert::success("La configuration a été mise à jour avec succès.");

            return redirect()->route('configurations.index');
        } catch (\Exception $e) {
            $this->logError('Erreur lors de la mise à jour de la configuration', $e, [
                'configuration_id' => $configuration->id,
                'data' => $request->all()
            ]);
            FlashAlert::error('Une erreur est survenue lors de la mise à jour de la configuration.');
            return back()->withInput();
        }
    }

    /**
     * Met à jour plusieurs configurations en masse (pour les couleurs, etc.)
     */
    public function updateBulk(Request $request)
    {
        try {
            // Validation basique
            $request->validate([
                'nom_paroisse' => 'nullable|string|max:255',
                'logo_path' => 'nullable|string|max:255',
                'couleur_primaire' => 'nullable|string|max:7',
                'couleur_secondaire' => 'nullable|string|max:7',
                'couleur_succes' => 'nullable|string|max:7',
                'couleur_info' => 'nullable|string|max:7',
                'couleur_avertissement' => 'nullable|string|max:7',
                'couleur_danger' => 'nullable|string|max:7',
                'couleur_bouton_ajout' => 'nullable|string|max:7',
                'couleur_bouton_ajout_hover' => 'nullable|string|max:7',
                'couleur_titre_page' => 'nullable|string|max:7',
                'couleur_action_voir' => 'nullable|string|max:7',
                'couleur_action_modifier' => 'nullable|string|max:7',
                'couleur_action_supprimer' => 'nullable|string|max:7',
                'phone_country' => 'nullable|string|max:5',
                'phone_dial_code' => 'nullable|string|max:10',
                'phone_regex' => 'nullable|string|max:255',
                'monnaie' => 'nullable|string|max:10',
                'format_date' => 'nullable|string|max:20',
                'format_heure' => 'nullable|string|max:20',
                'langue' => 'nullable|in:fr,en',
                // Configuration PDF
                'pdf_header_show_logo' => 'nullable|in:0,1',
                'pdf_header_logo' => 'nullable|string|max:255',
                'pdf_header_logo_width' => 'nullable|integer|min:20|max:200',
                'pdf_header_title' => 'nullable|string|max:255',
                'pdf_header_subtitle' => 'nullable|string|max:255',
                'pdf_header_address' => 'nullable|string|max:500',
                'pdf_header_phone' => 'nullable|string|max:50',
                'pdf_header_email' => 'nullable|email|max:255',
                'pdf_header_custom_text' => 'nullable|string|max:500',
                'pdf_header_bg_color' => 'nullable|string|max:7',
                'pdf_header_bg_color_text' => 'nullable|string|max:7',
                'pdf_header_text_color' => 'nullable|string|max:7',
                'pdf_header_text_color_text' => 'nullable|string|max:7',
                // Loader (chargement des pages)
                'loader_actif' => 'nullable|in:0,1',
                'loader_duree_min' => 'nullable|integer|min:1|max:60',
                'loader_afficher_logo' => 'nullable|in:0,1',
                'preloader_logo_path' => 'nullable|string|max:255',
                'loader_style' => 'nullable|in:logo_centre,logo_spinner,spinner_seul',
                'loader_position' => 'nullable|in:centre,haut,bas',
                'loader_couleur_fond' => 'nullable|string|max:7',
                'loader_couleur_texte' => 'nullable|string|max:7',
                // Page de connexion (image de fond, config globale)
                'login_bg_image' => 'nullable|string|max:500',
            ]);

            $data = $request->except(['_token', '_method']);
            $section = $data['section'] ?? null;
            unset($data['section']);

            // Page de connexion : config globale (paroisse_id null)
            $paroisseId = ($section === 'login') ? null : (auth()->check() ? (auth()->user()->paroisse_id ?? null) : null);

            // Gérer les champs de couleur avec texte (priorité au champ texte)
            if (isset($data['pdf_header_bg_color_text']) && !empty($data['pdf_header_bg_color_text'])) {
                $data['pdf_header_bg_color'] = $data['pdf_header_bg_color_text'];
            }
            unset($data['pdf_header_bg_color_text']);

            if (isset($data['pdf_header_text_color_text']) && !empty($data['pdf_header_text_color_text'])) {
                $data['pdf_header_text_color'] = $data['pdf_header_text_color_text'];
            }
            unset($data['pdf_header_text_color_text']);

            // Convertir pdf_header_show_logo en boolean
            if (isset($data['pdf_header_show_logo'])) {
                $data['pdf_header_show_logo'] = $data['pdf_header_show_logo'] === '1';
            }

            if (isset($data['loader_actif'])) {
                $data['loader_actif'] = $data['loader_actif'] === '1';
            }
            if (isset($data['loader_afficher_logo'])) {
                $data['loader_afficher_logo'] = $data['loader_afficher_logo'] === '1';
            }

            $updatedKeys = [];

            foreach ($data as $cle => $valeur) {
                $isLoginBg = ($section === 'login' && $cle === 'login_bg_image');
                $hasValue = $valeur !== null && $valeur !== '';

                if ($hasValue || $isLoginBg) {
                    $type = $this->detectType($valeur ?? '');
                    Configuration::setValue($paroisseId, $cle, $valeur ?? '', $type);
                    Cache::forget("config_{$paroisseId}_{$cle}");
                    $updatedKeys[] = $cle;
                }
            }

            if (empty($updatedKeys)) {
                FlashAlert::warning('Aucune configuration n\'a été mise à jour.');
            } else {
                $this->logInfo('Configurations mises à jour en masse', ['keys' => $updatedKeys, 'count' => count($updatedKeys)]);
                FlashAlert::success(count($updatedKeys) . " configuration(s) mise(s) à jour avec succès.");
            }

            return redirect()->route('configurations.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logError('Erreur de validation lors de la mise à jour en masse', $e, [
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            $this->logError('Erreur lors de la mise à jour en masse des configurations', $e, [
                'data' => $request->except(['_token', '_method']),
                'paroisse_id' => $paroisseId
            ]);
            FlashAlert::error('Une erreur est survenue lors de la mise à jour des configurations.');
            return back()->withInput();
        }
    }

    /**
     * Supprime une configuration (désactivation)
     */
    public function destroy(Configuration $configuration)
    {
        try {
            $configuration->update(['actif' => false]);
            Cache::forget("config_{$configuration->paroisse_id}_{$configuration->cle}");

            $this->logInfo("Configuration désactivée : {$configuration->cle}");
            FlashAlert::success("La configuration a été supprimée avec succès.");

            return redirect()->route('configurations.index');
        } catch (\Exception $e) {
            $this->logError('Erreur lors de la suppression de la configuration', $e, [
                'configuration_id' => $configuration->id
            ]);
            FlashAlert::error('Une erreur est survenue lors de la suppression de la configuration.');
            return back();
        }
    }

    /**
     * Détecte le type d'une valeur
     */
    private function detectType(mixed $valeur): string
    {
        if (is_bool($valeur)) {
            return 'boolean';
        }
        if (is_int($valeur)) {
            return 'integer';
        }
        if (is_float($valeur)) {
            return 'float';
        }
        if (is_array($valeur) || is_object($valeur)) {
            return 'json';
        }
        return 'string';
    }
}

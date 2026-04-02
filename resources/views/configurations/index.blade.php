@extends('layouts.app')

@section('title', 'Configuration')
@section('page-title', 'Configuration de l\'application')

@push('styles')
<style>
.page-config .card { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: none; }
.page-config .card-header { background: linear-gradient(135deg, var(--primary, #6A1B9A) 0%, #552586 100%); color: #fff; border-radius: 12px 12px 0 0; padding: 1.25rem 1.5rem; }
.page-config .card-title { font-weight: 600; font-size: 1.2rem; }
.page-config .nav-tabs { border-bottom: 2px solid #e9ecef; gap: 4px; flex-wrap: wrap; }
.page-config .nav-tabs .nav-link { border: none; border-radius: 8px 8px 0 0; padding: 12px 20px; font-weight: 500; color: #6c757d; background: #f8f9fa; transition: all 0.2s; }
.page-config .nav-tabs .nav-link:hover { color: var(--primary, #6A1B9A); background: #f0f0f0; }
.page-config .nav-tabs .nav-link.active { color: #fff; background: var(--primary, #6A1B9A); }
.page-config .tab-content { padding-top: 1.5rem; }
.page-config .form-label { font-weight: 600; color: #495057; margin-bottom: 0.5rem; }
.page-config .form-control { border-radius: 8px; border: 1px solid #dee2e6; }
.page-config .form-control:focus { border-color: var(--primary, #6A1B9A); box-shadow: 0 0 0 0.2rem rgba(106, 27, 154, 0.15); }
.page-config .form-control-color { width: 50px; height: 38px; border-radius: 8px; }
.page-config .btn-save { padding: 12px 30px; border-radius: 8px; font-weight: 600; }
.page-config .section-divider { border-top: 1px solid #e9ecef; padding-top: 1.5rem; margin-top: 1.5rem; }
.page-config .alert { border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="page-config">
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0 d-flex align-items-center">
                    <i class="fas fa-cogs me-3" style="font-size: 1.4rem; opacity: 0.9;"></i>
                    Paramètres de l'application
                </h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <h6 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreurs de validation</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Onglets Bootstrap --}}
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="identite-tab" data-bs-toggle="tab" data-bs-target="#identite" type="button" role="tab">
                            <i class="fas fa-church me-2"></i>Identité
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="couleurs-tab" data-bs-toggle="tab" data-bs-target="#couleurs" type="button" role="tab">
                            <i class="fas fa-palette me-2"></i>Couleurs
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="boutons-tab" data-bs-toggle="tab" data-bs-target="#boutons" type="button" role="tab">
                            <i class="fas fa-hand-pointer me-2"></i>Boutons
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="actions-tab" data-bs-toggle="tab" data-bs-target="#actions" type="button" role="tab">
                            <i class="fas fa-table me-2"></i>Actions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="generaux-tab" data-bs-toggle="tab" data-bs-target="#generaux" type="button" role="tab">
                            <i class="fas fa-sliders-h me-2"></i>Généraux
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="telephone-tab" data-bs-toggle="tab" data-bs-target="#telephone" type="button" role="tab">
                            <i class="fas fa-phone me-2"></i>Téléphone
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pdf-tab" data-bs-toggle="tab" data-bs-target="#pdf" type="button" role="tab">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="loader-tab" data-bs-toggle="tab" data-bs-target="#loader" type="button" role="tab">
                            <i class="fas fa-spinner me-2"></i>Loader
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                            <i class="fas fa-sign-in-alt me-2"></i>Page de connexion
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Section 1: Identité de la paroisse --}}
                    <div class="tab-pane fade show active" id="identite" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="identite">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom de la paroisse</label>
                                    <input type="text" name="nom_paroisse" class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'nom_paroisse') }}"
                                           placeholder="Ex: SAINT-ESPRIT DE MOUNGALI">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chemin du logo</label>
                                    <input type="text" name="logo_path" class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'logo_path') }}"
                                           placeholder="/images/logo-paroisse.svg">
                                    <small class="text-muted">Ex: /images/logo-paroisse.svg</small>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Section 2: Charte de couleurs --}}
                    <div class="tab-pane fade" id="couleurs" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="couleurs">

                            <div class="row">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur primaire</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_primaire" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_primaire') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_primaire') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur secondaire</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_secondaire" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_secondaire') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_secondaire') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur succès</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_succes" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_succes') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_succes') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur info</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_info" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_info') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_info') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur avertissement</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_avertissement" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_avertissement') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_avertissement') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur danger</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_danger" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_danger') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_danger') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Section 3: Boutons et titres --}}
                    <div class="tab-pane fade" id="boutons" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="boutons">

                            <div class="row">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur bouton d'ajout</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_bouton_ajout" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_bouton_ajout') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_bouton_ajout') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                    <small class="text-muted">Couleur des boutons "Ajouter"</small>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur bouton d'ajout (hover)</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_bouton_ajout_hover" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_bouton_ajout_hover') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_bouton_ajout_hover') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                    <small class="text-muted">Couleur au survol</small>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur titre de page</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_titre_page" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_titre_page') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_titre_page') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                    <small class="text-muted">Couleur des en-têtes de cartes</small>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Section 4: Actions du tableau --}}
                    <div class="tab-pane fade" id="actions" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="actions">

                            <div class="row">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur action "Voir"</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_action_voir" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_action_voir') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_action_voir') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                    <small class="text-muted">Bouton d'information</small>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur action "Modifier"</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_action_modifier" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_action_modifier') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_action_modifier') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                    <small class="text-muted">Bouton d'édition</small>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Couleur action "Supprimer"</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="couleur_action_supprimer" class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_action_supprimer') }}">
                                        <input type="text" value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'couleur_action_supprimer') }}"
                                               class="form-control" readonly style="flex: 1;">
                                    </div>
                                    <small class="text-muted">Bouton de suppression</small>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Section 5: Paramètres généraux --}}
                    <div class="tab-pane fade" id="generaux" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="generaux">

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Monnaie</label>
                                    <input type="text" name="monnaie" class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'monnaie') }}"
                                           placeholder="FCFA">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Format de date</label>
                                    <input type="text" name="format_date" class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'format_date') }}"
                                           placeholder="d/m/Y">
                                    <small class="text-muted">Ex: d/m/Y</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Format d'heure</label>
                                    <input type="text" name="format_heure" class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'format_heure') }}"
                                           placeholder="H:i">
                                    <small class="text-muted">Ex: H:i</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Langue</label>
                                    <select name="langue" class="form-control">
                                        <option value="fr" {{ \App\Helpers\ParoisseConfig::get($paroisseId, 'langue') == 'fr' ? 'selected' : '' }}>Français</option>
                                        <option value="en" {{ \App\Helpers\ParoisseConfig::get($paroisseId, 'langue') == 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Section 6: Téléphone / Pays --}}
                    <div class="tab-pane fade" id="telephone" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="telephone">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Pays (code ISO)</label>
                                    <input type="text"
                                           name="phone_country"
                                           class="form-control text-uppercase"
                                           data-transform="upper"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'phone_country', 'CG') }}"
                                           placeholder="Ex: CG">
                                    <small class="text-muted">Code pays ISO 2 lettres (ex: CG, FR, BE...)</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Indicatif téléphonique</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+</span>
                                        @php
                                            $dial = \App\Helpers\ParoisseConfig::get($paroisseId, 'phone_dial_code', '+242');
                                            $dial = ltrim($dial, '+');
                                        @endphp
                                        <input type="text"
                                               name="phone_dial_code"
                                               class="form-control"
                                               value="{{ $dial }}"
                                               placeholder="Ex: 242">
                                    </div>
                                    <small class="text-muted">Sans le + (ex: 242 pour Congo)</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Regex téléphone</label>
                                    <input type="text"
                                           name="phone_regex"
                                           class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'phone_regex', '/^(\+242|242|0)?[ \-]?[0-9]{9}$/') }}">
                                    <small class="text-muted">
                                        Expression régulière PHP pour valider le format.
                                    </small>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Section 7: En-tête PDF --}}
                    <div class="tab-pane fade" id="pdf" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="pdf">

                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Configurez l'apparence de l'en-tête des rapports financiers téléchargés en PDF.
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Afficher le logo</label>
                                    <select name="pdf_header_show_logo" class="form-control">
                                        <option value="1" @selected(\App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_show_logo', true))>Oui</option>
                                        <option value="0" @selected(!\App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_show_logo', true))>Non</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chemin du logo</label>
                                    <input type="text"
                                           name="pdf_header_logo"
                                           class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_logo', '') }}"
                                           placeholder="/images/logo-paroisse.png">
                                    <small class="text-muted">Chemin relatif depuis le dossier public</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Largeur du logo (px)</label>
                                    <input type="number"
                                           name="pdf_header_logo_width"
                                           class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_logo_width', '80') }}"
                                           min="20"
                                           max="200">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Titre de l'en-tête</label>
                                    <input type="text"
                                           name="pdf_header_title"
                                           class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_title', '') }}"
                                           placeholder="Laissez vide pour utiliser le nom de la paroisse">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sous-titre</label>
                                    <input type="text"
                                           name="pdf_header_subtitle"
                                           class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_subtitle', '') }}"
                                           placeholder="Ex: Paroisse catholique">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Adresse</label>
                                    <input type="text"
                                           name="pdf_header_address"
                                           class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_address', '') }}"
                                           placeholder="Laissez vide pour utiliser l'adresse de la paroisse">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="text"
                                           name="pdf_header_phone"
                                           class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_phone', '') }}"
                                           placeholder="Téléphone de la paroisse">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email"
                                           name="pdf_header_email"
                                           class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_email', '') }}"
                                           placeholder="Email de la paroisse">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Texte personnalisé</label>
                                    <textarea name="pdf_header_custom_text"
                                              class="form-control"
                                              rows="2"
                                              placeholder="Texte supplémentaire à afficher dans l'en-tête">{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_custom_text', '') }}</textarea>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Couleur de fond</label>
                                    <div class="d-flex gap-2">
                                        <input type="color"
                                               name="pdf_header_bg_color"
                                               class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_bg_color', '#003366') }}">
                                        <input type="text"
                                               name="pdf_header_bg_color_text"
                                               class="form-control"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_bg_color', '#003366') }}"
                                               placeholder="#003366">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Couleur du texte</label>
                                    <div class="d-flex gap-2">
                                        <input type="color"
                                               name="pdf_header_text_color"
                                               class="form-control form-control-color"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_text_color', '#FFFFFF') }}">
                                        <input type="text"
                                               name="pdf_header_text_color_text"
                                               class="form-control"
                                               value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'pdf_header_text_color', '#FFFFFF') }}"
                                               placeholder="#FFFFFF">
                                    </div>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Section 8: Loader --}}
                    <div class="tab-pane fade" id="loader" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="loader">

                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Écran de chargement affiché au démarrage des pages (logo + spinner).
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Activer le loader</label>
                                    <select name="loader_actif" class="form-control">
                                        <option value="1" @selected(\App\Helpers\ParoisseConfig::get($paroisseId, 'loader_actif', true))>Oui</option>
                                        <option value="0" @selected(!\App\Helpers\ParoisseConfig::get($paroisseId, 'loader_actif', true))>Non</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Logo du preloader</label>
                                    <input type="text" name="preloader_logo_path" class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get($paroisseId, 'preloader_logo_path', '') }}"
                                           placeholder="/images/logo-paroisse.svg">
                                    <small class="text-muted">Vide = logo principal (Identité)</small>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Section 9: Page de connexion (image de fond, config globale) --}}
                    <div class="tab-pane fade" id="login" role="tabpanel">
                        <form action="{{ route('configurations.update-bulk') }}" method="POST" class="config-section-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="login">

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Image de fond de la page de connexion</label>
                                    <input type="text" name="login_bg_image" class="form-control"
                                           value="{{ \App\Helpers\ParoisseConfig::get(null, 'login_bg_image') }}"
                                           placeholder="/images/fond-login.jpg">
                                    <small class="text-muted">Chemin vers une image (ex: /images/fond-login.jpg). Laisser vide pour garder le dégradé par défaut.</small>
                                </div>
                            </div>

                            <div class="section-divider text-end">
                                <button type="submit" class="btn btn-primary btn-save">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm create-help-panel" style="background: #f8fafc;">
            <div class="card-body p-4">
                <h6 class="mb-3 d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    En bref
                </h6>
                <p class="small text-muted mb-3">
                    Personnalisez l'application selon votre paroisse. Chaque onglet modifie un aspect différent.
                </p>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><strong>Identité</strong> — Nom et logo de la paroisse</li>
                    <li class="mb-2"><strong>Couleurs</strong> — Charte graphique (titres, boutons)</li>
                    <li class="mb-2"><strong>Boutons</strong> — Couleurs des boutons d'ajout</li>
                    <li class="mb-2"><strong>Actions</strong> — Couleurs des icônes du tableau</li>
                    <li class="mb-2"><strong>Généraux</strong> — Monnaie, format date, langue</li>
                    <li class="mb-2"><strong>Téléphone</strong> — Format des numéros</li>
                    <li class="mb-2"><strong>PDF</strong> — En-tête des rapports imprimés</li>
                    <li class="mb-2"><strong>Loader</strong> — Écran de chargement au démarrage</li>
                    <li class="mb-0"><strong>Connexion</strong> — Image de fond de la page login</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
            colorInput.addEventListener('input', function() {
                const container = this.closest('.d-flex');
                if (container) {
                    const textInput = container.querySelector('input[type="text"]');
                    if (textInput) {
                        textInput.value = this.value.toUpperCase();
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection

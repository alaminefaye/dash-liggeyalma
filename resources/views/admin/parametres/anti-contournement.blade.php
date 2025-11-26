@extends('layouts.app')

@section('title', 'Paramètres Anti-Contournement')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Configuration Anti-Contournement</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.parametres.anti-contournement.update') }}" method="POST">
            @csrf

            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="mb-3">Activation du Système</h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="detection_contournement_active" value="1" 
                                   id="detection_contournement_active" 
                                   {{ ($parametres['detection_contournement_active']->valeur ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="detection_contournement_active">
                                Activer la détection de contournement
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="blocage_automatique" value="1" 
                                   id="blocage_automatique" 
                                   {{ ($parametres['blocage_automatique']->valeur ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="blocage_automatique">
                                Blocage automatique des comptes
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="mb-3">Seuils et Limites</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre de contournements avant blocage définitif</label>
                        <input type="number" name="nombre_contournements_avant_blocage" class="form-control" 
                               value="{{ $parametres['nombre_contournements_avant_blocage']->valeur ?? '3' }}" 
                               min="1" max="10" required>
                        <small class="text-muted">Après ce nombre de contournements confirmés, le compte sera bloqué automatiquement</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Seuil de solde négatif pour blocage (FCFA)</label>
                        <input type="number" name="seuil_solde_negatif" class="form-control" 
                               value="{{ $parametres['seuil_solde_negatif']->valeur ?? '0' }}" 
                               min="0" step="0.01" required>
                        <small class="text-muted">Si le solde d'un prestataire est inférieur à ce montant, le compte sera bloqué</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Taux de commission par défaut (%)</label>
                        <input type="number" name="commission_par_defaut" class="form-control" 
                               value="{{ $parametres['commission_par_defaut']->valeur ?? '10' }}" 
                               min="0" max="100" step="0.01" required>
                        <small class="text-muted">Taux appliqué par défaut si non spécifié par catégorie</small>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="mb-3">Communication</h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="appel_masque_active" value="1" 
                                   id="appel_masque_active" 
                                   {{ ($parametres['appel_masque_active']->valeur ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="appel_masque_active">
                                Activer l'appel masqué
                            </label>
                            <small class="d-block text-muted">Les numéros de téléphone seront masqués lors des appels via l'application</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="partage_numero_active" value="1" 
                                   id="partage_numero_active" 
                                   {{ ($parametres['partage_numero_active']->valeur ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="partage_numero_active">
                                Autoriser le partage de numéro
                            </label>
                            <small class="d-block text-muted">Si désactivé, les utilisateurs ne pourront pas voir les numéros de téléphone avant la finalisation de la commande</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('admin.parametres.index') }}" class="btn btn-label-secondary me-2">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


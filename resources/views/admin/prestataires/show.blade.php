@extends('layouts.app')

@section('title', 'Détails Prestataire')

@section('content')
<div class="row">
    <!-- Informations Prestataire -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mb-3">
                    <img src="{{ $prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-100 h-auto rounded-circle" />
                </div>
                <h4 class="mb-1">{{ $prestataire->user->name }}</h4>
                <p class="text-muted mb-2">{{ $prestataire->metier }}</p>
                <p class="text-muted mb-3">{{ $prestataire->user->email }}</p>
                
                <div class="mb-3">
                    @if($prestataire->statut_inscription === 'en_attente')
                        <span class="badge bg-label-warning">En attente de validation</span>
                    @elseif($prestataire->statut_inscription === 'valide')
                        <span class="badge bg-label-success">Compte validé</span>
                    @else
                        <span class="badge bg-label-danger">Compte refusé</span>
                    @endif
                </div>

                <div class="mb-3">
                    @php
                        $statusColors = [
                            'active' => 'success',
                            'suspended' => 'warning',
                            'blocked' => 'danger',
                        ];
                        $color = $statusColors[$prestataire->user->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-label-{{ $color }}">{{ ucfirst($prestataire->user->status) }}</span>
                </div>

                @if($prestataire->solde < 0)
                    <div class="alert alert-danger mb-3">
                        <strong>⚠️ Solde négatif :</strong><br>
                        {{ number_format($prestataire->solde, 0, ',', ' ') }} FCFA
                    </div>
                @else
                    <div class="mb-3">
                        <strong>Solde :</strong><br>
                        <span class="text-success">{{ number_format($prestataire->solde, 0, ',', ' ') }} FCFA</span>
                    </div>
                @endif
                
                <div class="d-flex flex-column gap-2">
                    @if($prestataire->statut_inscription === 'en_attente')
                        <form action="{{ route('admin.prestataires.validate', $prestataire) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Valider ce compte prestataire ?')">
                                <i class="bx bx-check"></i> Valider le compte
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bx bx-x"></i> Refuser le compte
                        </button>
                        <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#requestDocumentsModal">
                            <i class="bx bx-file"></i> Demander documents
                        </button>
                    @endif

                    <a href="{{ route('admin.prestataires.edit', $prestataire) }}" class="btn btn-primary w-100">
                        <i class="bx bx-edit"></i> Modifier
                    </a>

                    @if($prestataire->user->status === 'active')
                        <form action="{{ route('admin.prestataires.suspend', $prestataire) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Suspendre ce prestataire ?')">
                                <i class="bx bx-pause"></i> Suspendre
                            </button>
                        </form>
                    @elseif($prestataire->user->status === 'suspended')
                        <form action="{{ route('admin.prestataires.activate', $prestataire) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Réactiver ce prestataire ?')">
                                <i class="bx bx-check"></i> Réactiver
                            </button>
                        </form>
                    @endif

                    @if($prestataire->user->status !== 'blocked')
                        <form action="{{ route('admin.prestataires.block', $prestataire) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bloquer ce prestataire ? Cette action est irréversible.')">
                                <i class="bx bx-block"></i> Bloquer
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.prestataires.unblock', $prestataire) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Débloquer ce prestataire ?')">
                                <i class="bx bx-check"></i> Débloquer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Interventions totales</small>
                    <h4 class="mb-0">{{ $stats['interventions_total'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Interventions terminées</small>
                    <h4 class="mb-0">{{ $stats['interventions_terminees'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Avis reçus</small>
                    <h4 class="mb-0">{{ $stats['avis_recus'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Note moyenne</small>
                    <h4 class="mb-0">
                        @if($stats['note_moyenne'] > 0)
                            {{ number_format($stats['note_moyenne'], 1) }}/5.0
                        @else
                            N/A
                        @endif
                    </h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Revenus total</small>
                    <h4 class="mb-0">{{ number_format($stats['revenus_total'], 0, ',', ' ') }} FCFA</h4>
                </div>
                <div>
                    <small class="text-muted">Commission payée</small>
                    <h4 class="mb-0">{{ number_format($stats['commission_payee'], 0, ',', ' ') }} FCFA</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails -->
    <div class="col-lg-8">
        <!-- Informations Personnelles -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nom complet :</strong>
                        <p>{{ $prestataire->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email :</strong>
                        <p>{{ $prestataire->user->email }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Téléphone :</strong>
                        <p>{{ $prestataire->user->phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date d'inscription :</strong>
                        <p>{{ $prestataire->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations Professionnelles -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Informations Professionnelles</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Métier :</strong>
                        <p>{{ $prestataire->metier }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Années d'expérience :</strong>
                        <p>{{ $prestataire->annees_experience }} ans</p>
                    </div>
                </div>
                @if($prestataire->specialites)
                <div class="mb-3">
                    <strong>Spécialités :</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($prestataire->specialites as $specialite)
                            <span class="badge bg-label-primary">{{ $specialite }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($prestataire->description)
                <div class="mb-3">
                    <strong>Description :</strong>
                    <p>{{ $prestataire->description }}</p>
                </div>
                @endif
                <div class="row">
                    <div class="col-md-4">
                        <strong>Tarif horaire :</strong>
                        <p>{{ $prestataire->tarif_horaire ? number_format($prestataire->tarif_horaire, 0, ',', ' ') . ' FCFA/h' : '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong>Frais de déplacement :</strong>
                        <p>{{ number_format($prestataire->frais_deplacement, 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="col-md-4">
                        <strong>Score de confiance :</strong>
                        <p>
                            <span class="badge bg-label-primary">{{ number_format($prestataire->score_confiance, 1) }}/5.0</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        @if($prestataire->documents)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Documents</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($prestataire->documents as $type => $path)
                    <div class="col-md-6 mb-3">
                        <strong>{{ ucfirst(str_replace('_', ' ', $type)) }} :</strong>
                        <div class="mt-2">
                            <a href="{{ asset('storage/' . $path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-download"></i> Voir le document
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Historique des Interventions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Historique des Interventions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prestataire->commandes()->latest()->limit(10)->get() as $commande)
                            <tr>
                                <td>#{{ $commande->id }}</td>
                                <td>{{ $commande->created_at->format('d/m/Y') }}</td>
                                <td>{{ $commande->client->user->name ?? 'N/A' }}</td>
                                <td>{{ $commande->categorieService->nom ?? 'N/A' }}</td>
                                <td>{{ number_format($commande->montant_total ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    <span class="badge bg-label-{{ $commande->statut === 'terminee' ? 'success' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.commandes.show', $commande) }}" class="btn btn-sm btn-info">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucune intervention</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Avis Reçus -->
        @if($prestataire->avis->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Avis Reçus</h5>
            </div>
            <div class="card-body">
                @foreach($prestataire->avis()->latest()->limit(5)->get() as $avi)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>{{ $avi->client->user->name ?? 'Client anonyme' }}</strong>
                        <div>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bx {{ $i <= $avi->note ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                            @endfor
                        </div>
                    </div>
                    @if($avi->commentaire)
                        <p class="mb-0">{{ $avi->commentaire }}</p>
                    @endif
                    <small class="text-muted">{{ $avi->created_at->format('d/m/Y') }}</small>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Contournements Détectés -->
        @if($prestataire->contournements->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">
                    ⚠️ Contournements Détectés ({{ $prestataire->contournements->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prestataire->contournements()->latest()->limit(5)->get() as $contournement)
                            <tr>
                                <td>{{ $contournement->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-label-danger">{{ ucfirst(str_replace('_', ' ', $contournement->type)) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $contournement->statut === 'confirme' ? 'danger' : 'warning' }}">
                                        {{ ucfirst($contournement->statut) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.contournements.show', $contournement) }}" class="btn btn-sm btn-info">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Demander Documents -->
@if($prestataire->statut_inscription === 'en_attente')
<div class="modal fade" id="requestDocumentsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.prestataires.request-documents', $prestataire) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Demander des documents supplémentaires</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Documents requis <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="documents_requis[]" value="piece_identite" id="doc_piece">
                            <label class="form-check-label" for="doc_piece">Pièce d'identité</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="documents_requis[]" value="certificat_travail" id="doc_certif">
                            <label class="form-check-label" for="doc_certif">Certificat de travail</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="documents_requis[]" value="diplome" id="doc_diplome">
                            <label class="form-check-label" for="doc_diplome">Diplôme</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="documents_requis[]" value="autre" id="doc_autre">
                            <label class="form-check-label" for="doc_autre">Autre</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="4" required placeholder="Expliquez quels documents supplémentaires sont nécessaires..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info">Envoyer la demande</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Refus -->
@if($prestataire->statut_inscription === 'en_attente')
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.prestataires.reject', $prestataire) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Refuser le compte prestataire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
                        <textarea name="motif" class="form-control" rows="4" required placeholder="Expliquez pourquoi ce compte est refusé..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection


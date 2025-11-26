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
    </div>
</div>

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


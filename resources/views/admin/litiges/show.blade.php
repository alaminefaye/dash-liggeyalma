@extends('layouts.app')

@section('title', 'Détails Litige')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Litige #{{ $litige->id }}</h5>
                <div>
                    @php
                        $statusColors = [
                            'en_attente' => 'warning',
                            'en_cours' => 'info',
                            'resolu' => 'success',
                            'clos' => 'secondary',
                        ];
                        $statusColor = $statusColors[$litige->statut] ?? 'secondary';
                    @endphp
                    <span class="badge bg-label-{{ $statusColor }}" style="font-size: 1rem;">
                        {{ ucfirst(str_replace('_', ' ', $litige->statut)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Type de litige :</strong>
                        <p>
                            <span class="badge bg-label-info">{{ ucfirst($litige->type) }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date de création :</strong>
                        <p>{{ $litige->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Client :</strong>
                        <p>
                            <a href="{{ route('admin.clients.show', $litige->client) }}">
                                {{ $litige->client->user->name }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Prestataire :</strong>
                        <p>
                            <a href="{{ route('admin.prestataires.show', $litige->prestataire) }}">
                                {{ $litige->prestataire->user->name }}
                            </a>
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Commande associée :</strong>
                    <p>
                        <a href="{{ route('admin.commandes.show', $litige->commande) }}" class="btn btn-sm btn-outline-primary">
                            Voir la commande #{{ $litige->commande_id }}
                        </a>
                    </p>
                </div>

                <div class="mb-3">
                    <strong>Description du problème :</strong>
                    <p class="mt-2">{{ $litige->description }}</p>
                </div>

                @if($litige->preuves && count($litige->preuves) > 0)
                <div class="mb-3">
                    <strong>Preuves :</strong>
                    <div class="mt-2">
                        @foreach($litige->preuves as $key => $preuve)
                            <div class="alert alert-info">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }} :</strong>
                                @if(is_array($preuve))
                                    <pre class="mb-0">{{ json_encode($preuve, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    <p class="mb-0">{{ $preuve }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($litige->resolution)
                <div class="alert alert-success">
                    <strong>Résolution :</strong>
                    <p class="mb-0 mt-2">{{ $litige->resolution }}</p>
                    @if($litige->traitePar)
                        <small class="text-muted">Traité par {{ $litige->traitePar->name }} le {{ $litige->traite_le->format('d/m/Y à H:i') }}</small>
                    @endif
                </div>
                @endif

                @if($litige->decision)
                <div class="mb-3">
                    <strong>Décision :</strong>
                    <p>
                        @php
                            $decisionColors = [
                                'remboursement' => 'danger',
                                'remediation' => 'warning',
                                'rejet' => 'secondary',
                            ];
                            $decisionColor = $decisionColors[$litige->decision] ?? 'secondary';
                        @endphp
                        <span class="badge bg-label-{{ $decisionColor }}">{{ ucfirst($litige->decision) }}</span>
                    </p>
                    @if($litige->montant_remboursement)
                        <p><strong>Montant remboursé :</strong> {{ number_format($litige->montant_remboursement, 0, ',', ' ') }} FCFA</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Actions</h5>
            </div>
            <div class="card-body">
                @if($litige->statut === 'en_attente' || $litige->statut === 'en_cours')
                    <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#processModal">
                        <i class="bx bx-check"></i> Traiter le litige
                    </button>
                    <button type="button" class="btn btn-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#mediationModal">
                        <i class="bx bx-message"></i> Médiation
                    </button>
                @endif

                @if($litige->statut === 'en_cours')
                    <form action="{{ route('admin.litiges.resolve', $litige) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#resolveModal">
                            <i class="bx bx-check-circle"></i> Résoudre
                        </button>
                    </form>
                @endif

                @if($litige->statut !== 'clos')
                    <form action="{{ route('admin.litiges.close', $litige) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-100" onclick="return confirm('Clôturer ce litige ?')">
                            <i class="bx bx-x"></i> Clôturer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Traiter -->
<div class="modal fade" id="processModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.litiges.process', $litige) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Traiter le litige</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Résolution <span class="text-danger">*</span></label>
                        <textarea name="resolution" class="form-control" rows="4" required placeholder="Décrivez la résolution du litige..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Décision <span class="text-danger">*</span></label>
                        <select name="decision" class="form-select" required>
                            <option value="">Sélectionner une décision</option>
                            <option value="remboursement">Remboursement</option>
                            <option value="remediation">Remédiation</option>
                            <option value="rejet">Rejet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant remboursement (si applicable)</label>
                        <input type="number" name="montant_remboursement" step="0.01" min="0" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Traiter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Résoudre -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.litiges.resolve', $litige) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Résoudre le litige</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Résolution finale <span class="text-danger">*</span></label>
                        <textarea name="resolution" class="form-control" rows="4" required placeholder="Décrivez la résolution finale..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Résoudre</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Médiation -->
<div class="modal fade" id="mediationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Médiation - Contacter les parties</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6>Client</h6>
                                <p class="mb-1"><strong>{{ $litige->client->user->name }}</strong></p>
                                <p class="mb-1"><small>{{ $litige->client->user->email }}</small></p>
                                <p class="mb-0"><small>{{ $litige->client->user->phone ?? 'N/A' }}</small></p>
                                <a href="mailto:{{ $litige->client->user->email }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="bx bx-envelope"></i> Envoyer email
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6>Prestataire</h6>
                                <p class="mb-1"><strong>{{ $litige->prestataire->user->name }}</strong></p>
                                <p class="mb-1"><small>{{ $litige->prestataire->user->email }}</small></p>
                                <p class="mb-0"><small>{{ $litige->prestataire->user->phone ?? 'N/A' }}</small></p>
                                <a href="mailto:{{ $litige->prestataire->user->email }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="bx bx-envelope"></i> Envoyer email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message de médiation</label>
                    <textarea class="form-control" rows="4" placeholder="Rédigez un message de médiation pour les deux parties..."></textarea>
                </div>
                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i> 
                    Vous pouvez contacter les deux parties par email ou téléphone pour résoudre ce litige.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-info">
                    <i class="bx bx-send"></i> Envoyer message
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Détails Commande')

@push('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Informations Commande -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Commande #{{ $commande->id }}</h5>
                <div class="d-flex gap-2">
                    @if($commande->statut !== 'terminee' && $commande->statut !== 'annulee')
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                            <i class="bx bx-edit"></i> Modifier statut
                        </button>
                    @endif
                    @if($commande->statut !== 'annulee' && $commande->statut !== 'terminee')
                        <form action="{{ route('admin.commandes.cancel', $commande) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Annuler cette commande ?')">
                                <i class="bx bx-x"></i> Annuler
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Statut :</strong>
                        <div class="mt-1">
                            @php
                                $statusColors = [
                                    'en_attente' => 'warning',
                                    'acceptee' => 'info',
                                    'en_route' => 'primary',
                                    'arrivee' => 'primary',
                                    'en_cours' => 'info',
                                    'terminee' => 'success',
                                    'annulee' => 'danger',
                                ];
                                $color = $statusColors[$commande->statut] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $color }}" style="font-size: 1rem;">
                                {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <strong>Type de commande :</strong>
                        <p>{{ $commande->type_commande === 'immediate' ? 'Intervention immédiate' : 'Réservation programmée' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Client :</strong>
                        <div class="d-flex align-items-center mt-1">
                            <div class="avatar avatar-sm me-2">
                                <img src="{{ $commande->client->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $commande->client->user->name }}</h6>
                                <small class="text-muted">{{ $commande->client->user->email }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <strong>Prestataire :</strong>
                        @if($commande->prestataire)
                            <div class="d-flex align-items-center mt-1">
                                <div class="avatar avatar-sm me-2">
                                    <img src="{{ $commande->prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $commande->prestataire->user->name }}</h6>
                                    <small class="text-muted">{{ $commande->prestataire->metier }}</small>
                                </div>
                            </div>
                        @else
                            <p class="text-muted mt-1">En attente d'acceptation</p>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Service :</strong>
                    <p>{{ $commande->categorieService->nom ?? 'N/A' }}</p>
                    @if($commande->sousCategorieService)
                        <small class="text-muted">{{ $commande->sousCategorieService->nom }}</small>
                    @endif
                </div>

                <div class="mb-3">
                    <strong>Description :</strong>
                    <p>{{ $commande->description }}</p>
                </div>

                @if($commande->photos && count($commande->photos) > 0)
                <div class="mb-3">
                    <strong>Photos :</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($commande->photos as $photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="Photo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <strong>Adresse d'intervention :</strong>
                    <p>{{ $commande->adresse_intervention }}</p>
                    @if($commande->latitude && $commande->longitude)
                        <small class="text-muted">Coordonnées: {{ $commande->latitude }}, {{ $commande->longitude }}</small>
                    @endif
                </div>

                @if($commande->date_heure_souhaitee)
                <div class="mb-3">
                    <strong>Date/heure souhaitée :</strong>
                    <p>{{ $commande->date_heure_souhaitee->format('d/m/Y à H:i') }}</p>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <strong>Montant total :</strong>
                        <h4 class="text-primary">{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</h4>
                    </div>
                    <div class="col-md-4">
                        <strong>Commission :</strong>
                        <h4 class="text-info">{{ number_format($commande->montant_commission, 0, ',', ' ') }} FCFA</h4>
                    </div>
                    <div class="col-md-4">
                        <strong>Méthode de paiement :</strong>
                        <p>
                            @if($commande->methode_paiement)
                                <span class="badge bg-label-primary">{{ ucfirst(str_replace('_', ' ', $commande->methode_paiement)) }}</span>
                            @else
                                <span class="text-muted">Non défini</span>
                            @endif
                        </p>
                        <strong>Statut paiement :</strong>
                        <p>
                            @php
                                $paymentColors = [
                                    'en_attente' => 'warning',
                                    'paye' => 'success',
                                    'rembourse' => 'danger',
                                ];
                                $paymentColor = $paymentColors[$commande->statut_paiement] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $paymentColor }}">{{ ucfirst($commande->statut_paiement) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des statuts -->
        @if($commande->historique_statuts && count($commande->historique_statuts) > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Historique des Statuts</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($commande->historique_statuts as $historique)
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <i class="bx bx-check"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">
                                    {{ ucfirst(str_replace('_', ' ', $historique['nouveau_statut'])) }}
                                </h6>
                                <p class="mb-0 text-muted">
                                    <small>{{ \Carbon\Carbon::parse($historique['date'])->format('d/m/Y à H:i') }}</small>
                                    @if(isset($historique['par']))
                                        <br><small>Par: {{ $historique['par'] }}</small>
                                    @endif
                                    @if(isset($historique['raison']))
                                        <br><small>Raison: {{ $historique['raison'] }}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Messages/Chat -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Messages / Chat</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i> 
                    <strong>Note :</strong> Le système de chat sera disponible une fois l'API mobile connectée.
                </div>
                @php
                    // Simuler des messages pour la démo (à remplacer par la vraie logique)
                    $messages = [
                        ['user' => 'Client', 'message' => 'Bonjour, pouvez-vous venir aujourd\'hui ?', 'date' => now()->subHours(2)],
                        ['user' => 'Prestataire', 'message' => 'Oui, je serai là à 14h', 'date' => now()->subHours(1)],
                    ];
                @endphp
                @if(count($messages) > 0)
                    <div class="chat-messages" style="max-height: 300px; overflow-y: auto;">
                        @foreach($messages as $msg)
                        <div class="mb-3 p-2 {{ $msg['user'] === 'Client' ? 'bg-light' : 'bg-primary bg-opacity-10' }} rounded">
                            <div class="d-flex justify-content-between mb-1">
                                <strong>{{ $msg['user'] }}</strong>
                                <small class="text-muted">{{ $msg['date']->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-0">{{ $msg['message'] }}</p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center">Aucun message pour cette commande</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.clients.show', $commande->client) }}" class="btn btn-outline-primary">
                        <i class="bx bx-user"></i> Voir le client
                    </a>
                    @if($commande->prestataire)
                        <a href="{{ route('admin.prestataires.show', $commande->prestataire) }}" class="btn btn-outline-primary">
                            <i class="bx bx-briefcase"></i> Voir le prestataire
                        </a>
                    @endif
                    @if($commande->statut === 'terminee')
                        <a href="{{ route('admin.commandes.invoice', $commande) }}" class="btn btn-outline-info" target="_blank">
                            <i class="bx bx-file"></i> Générer facture
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction -->
        @if($commande->transaction)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Transaction</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Montant :</strong>
                    <p>{{ number_format($commande->transaction->montant, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="mb-2">
                    <strong>Commission :</strong>
                    <p>{{ number_format($commande->transaction->commission, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="mb-2">
                    <strong>Méthode :</strong>
                    <p>{{ ucfirst(str_replace('_', ' ', $commande->transaction->methode_paiement ?? 'N/A')) }}</p>
                </div>
                <div>
                    <strong>Statut :</strong>
                    <p>
                        @php
                            $transactionColors = [
                                'en_attente' => 'warning',
                                'validee' => 'success',
                                'refusee' => 'danger',
                            ];
                            $transactionColor = $transactionColors[$commande->transaction->statut] ?? 'secondary';
                        @endphp
                        <span class="badge bg-label-{{ $transactionColor }}">{{ ucfirst($commande->transaction->statut) }}</span>
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Avis -->
        @if($commande->avis)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Avis Client</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Note :</strong>
                    <div>
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bx {{ $i <= $commande->avis->note ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                        @endfor
                    </div>
                </div>
                @if($commande->avis->commentaire)
                <div>
                    <strong>Commentaire :</strong>
                    <p>{{ $commande->avis->commentaire }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Modifier Statut -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.commandes.update-status', $commande) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le statut de la commande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nouveau statut <span class="text-danger">*</span></label>
                        <select name="statut" class="form-select" required>
                            <option value="en_attente" {{ $commande->statut == 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="acceptee" {{ $commande->statut == 'acceptee' ? 'selected' : '' }}>Acceptée</option>
                            <option value="en_route" {{ $commande->statut == 'en_route' ? 'selected' : '' }}>En route</option>
                            <option value="arrivee" {{ $commande->statut == 'arrivee' ? 'selected' : '' }}>Arrivée</option>
                            <option value="en_cours" {{ $commande->statut == 'en_cours' ? 'selected' : '' }}>En cours</option>
                            <option value="terminee" {{ $commande->statut == 'terminee' ? 'selected' : '' }}>Terminée</option>
                            <option value="annulee" {{ $commande->statut == 'annulee' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


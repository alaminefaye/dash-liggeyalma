@extends('layouts.app')

@section('title', 'Détails Client')

@section('content')
<div class="row">
    <!-- Informations Client -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mb-3">
                    <img src="{{ $client->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-100 h-auto rounded-circle" />
                </div>
                <h4 class="mb-1">{{ $client->user->name }}</h4>
                <p class="text-muted mb-3">{{ $client->user->email }}</p>
                @php
                    $statusColors = [
                        'active' => 'success',
                        'suspended' => 'warning',
                        'blocked' => 'danger',
                    ];
                    $color = $statusColors[$client->user->status] ?? 'secondary';
                @endphp
                <span class="badge bg-label-{{ $color }} mb-3">{{ ucfirst($client->user->status) }}</span>
                
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary">
                        <i class="bx bx-edit"></i> Modifier
                    </a>
                    @if($client->user->status === 'active')
                        <form action="{{ route('admin.clients.suspend', $client) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Suspendre ce client ?')">
                                <i class="bx bx-pause"></i> Suspendre
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.clients.activate', $client) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Réactiver ce client ?')">
                                <i class="bx bx-check"></i> Réactiver
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
                    <small class="text-muted">Commandes totales</small>
                    <h4 class="mb-0">{{ $stats['commandes_total'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Commandes terminées</small>
                    <h4 class="mb-0">{{ $stats['commandes_terminees'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Avis donnés</small>
                    <h4 class="mb-0">{{ $stats['avis_donnes'] }}</h4>
                </div>
                <div>
                    <small class="text-muted">Montant total dépensé</small>
                    <h4 class="mb-0">{{ number_format($stats['montant_total'], 0, ',', ' ') }} FCFA</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nom complet :</strong>
                        <p>{{ $client->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email :</strong>
                        <p>{{ $client->user->email }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Téléphone :</strong>
                        <p>{{ $client->user->phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Adresse :</strong>
                        <p>{{ $client->address ?? '-' }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Date d'inscription :</strong>
                        <p>{{ $client->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Score de confiance :</strong>
                        <p>
                            <span class="badge bg-label-primary">{{ number_format($client->score_confiance, 1) }}/5.0</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des Commandes -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Historique des Commandes</h5>
                <a href="{{ route('admin.commandes.index', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                @if($client->commandes->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Service</th>
                                    <th>Prestataire</th>
                                    <th>Statut</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->commandes->take(10) as $commande)
                                <tr>
                                    <td>#{{ $commande->id }}</td>
                                    <td>{{ $commande->categorieService->nom ?? 'N/A' }}</td>
                                    <td>{{ $commande->prestataire->user->name ?? 'En attente' }}</td>
                                    <td>
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
                                        <span class="badge bg-label-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $commande->statut)) }}</span>
                                    </td>
                                    <td>{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $commande->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.commandes.show', $commande) }}" class="btn btn-sm btn-primary">Voir</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">Aucune commande</p>
                @endif
            </div>
        </div>

        <!-- Historique des Paiements -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Historique des Paiements</h5>
            </div>
            <div class="card-body">
                @php
                    $transactions = \App\Models\Transaction::where('client_id', $client->id)
                        ->latest()
                        ->limit(10)
                        ->get();
                @endphp
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-label-info">{{ ucfirst($transaction->type) }}</span>
                                    </td>
                                    <td>{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->methode_paiement ?? 'N/A')) }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $transaction->statut === 'validee' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->statut) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">Aucun paiement</p>
                @endif
            </div>
        </div>

        <!-- Avis Donnés -->
        @php
            $avis = \App\Models\Avis::where('client_id', $client->id)
                ->latest()
                ->limit(5)
                ->get();
        @endphp
        @if($avis->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Avis Donnés</h5>
            </div>
            <div class="card-body">
                @foreach($avis as $avi)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>{{ $avi->prestataire->user->name ?? 'Prestataire' }}</strong>
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

        <!-- Tentatives de Contournement -->
        @php
            $contournements = \App\Models\Contournement::where('client_id', $client->id)
                ->latest()
                ->get();
        @endphp
        @if($contournements->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">
                    ⚠️ Tentatives de Contournement ({{ $contournements->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Prestataire</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contournements as $contournement)
                            <tr>
                                <td>{{ $contournement->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-label-danger">{{ ucfirst(str_replace('_', ' ', $contournement->type)) }}</span>
                                </td>
                                <td>{{ $contournement->prestataire->user->name ?? 'N/A' }}</td>
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
@endsection


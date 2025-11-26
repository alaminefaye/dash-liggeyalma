@extends('layouts.app')

@section('title', 'Gestion des Commandes')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Liste des Commandes</h5>
        <a href="{{ route('admin.commandes.export') }}" class="btn btn-success">
            <i class="bx bx-file"></i> Export Excel
        </a>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.commandes.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (ID, client, prestataire)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="acceptee" {{ request('statut') == 'acceptee' ? 'selected' : '' }}>Acceptée</option>
                        <option value="en_route" {{ request('statut') == 'en_route' ? 'selected' : '' }}>En route</option>
                        <option value="arrivee" {{ request('statut') == 'arrivee' ? 'selected' : '' }}>Arrivée</option>
                        <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="terminee" {{ request('statut') == 'terminee' ? 'selected' : '' }}>Terminée</option>
                        <option value="annulee" {{ request('statut') == 'annulee' ? 'selected' : '' }}>Annulée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="client_id" class="form-select">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="categorie_id" class="form-select">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $categorie)
                            <option value="{{ $categorie->id }}" {{ request('categorie_id') == $categorie->id ? 'selected' : '' }}>
                                {{ $categorie->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="date" name="date_from" class="form-control" placeholder="Date début" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1">
                    <input type="date" name="date_to" class="form-control" placeholder="Date fin" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Prestataire</th>
                        <th>Service</th>
                        <th>Statut</th>
                        <th>Montant</th>
                        <th>Paiement</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commandes as $commande)
                    <tr>
                        <td>#{{ $commande->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <img src="{{ $commande->client->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $commande->client->user->name }}</h6>
                                    <small class="text-muted">{{ $commande->client->user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($commande->prestataire)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="{{ $commande->prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $commande->prestataire->user->name }}</h6>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">En attente</span>
                            @endif
                        </td>
                        <td>{{ $commande->categorieService->nom ?? 'N/A' }}</td>
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
                        <td>
                            @php
                                $paymentColors = [
                                    'en_attente' => 'warning',
                                    'paye' => 'success',
                                    'rembourse' => 'danger',
                                ];
                                $paymentColor = $paymentColors[$commande->statut_paiement] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $paymentColor }}">{{ ucfirst($commande->statut_paiement) }}</span>
                        </td>
                        <td>{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.commandes.show', $commande) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Aucune commande trouvée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $commandes->links() }}
        </div>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Gestion des Litiges')

@section('content')
<div class="row mb-4">
    <!-- Statistiques -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Total Litiges</span>
                <h3 class="card-title mb-2">{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">En attente</span>
                <h3 class="card-title mb-2 text-warning">{{ $stats['en_attente'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">En cours</span>
                <h3 class="card-title mb-2 text-info">{{ $stats['en_cours'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Résolus</span>
                <h3 class="card-title mb-2 text-success">{{ $stats['resolus'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Liste des Litiges</h5>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.litiges.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (ID, client, prestataire)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="resolu" {{ request('statut') == 'resolu' ? 'selected' : '' }}>Résolu</option>
                        <option value="clos" {{ request('statut') == 'clos' ? 'selected' : '' }}>Clos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="qualite" {{ request('type') == 'qualite' ? 'selected' : '' }}>Qualité</option>
                        <option value="ponctualite" {{ request('type') == 'ponctualite' ? 'selected' : '' }}>Ponctualité</option>
                        <option value="prix" {{ request('type') == 'prix' ? 'selected' : '' }}>Prix</option>
                        <option value="comportement" {{ request('type') == 'comportement' ? 'selected' : '' }}>Comportement</option>
                        <option value="autre" {{ request('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-search"></i> Rechercher
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
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Prestataire</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($litiges as $litige)
                    <tr>
                        <td>#{{ $litige->id }}</td>
                        <td>
                            <a href="{{ route('admin.commandes.show', $litige->commande) }}">
                                Commande #{{ $litige->commande_id }}
                            </a>
                        </td>
                        <td>{{ $litige->client->user->name ?? 'N/A' }}</td>
                        <td>{{ $litige->prestataire->user->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-label-info">{{ ucfirst($litige->type) }}</span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'en_attente' => 'warning',
                                    'en_cours' => 'info',
                                    'resolu' => 'success',
                                    'clos' => 'secondary',
                                ];
                                $color = $statusColors[$litige->statut] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $litige->statut)) }}</span>
                        </td>
                        <td>{{ $litige->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.litiges.show', $litige) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Aucun litige trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $litiges->links() }}
        </div>
    </div>
</div>
@endsection


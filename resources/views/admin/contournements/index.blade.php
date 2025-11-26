@extends('layouts.app')

@section('title', 'Gestion Anti-Contournement')

@section('content')
<div class="row mb-4">
    <!-- Statistiques -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Total</span>
                <h3 class="card-title mb-2">{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Détectés</span>
                <h3 class="card-title mb-2 text-warning">{{ $stats['detectes'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Confirmés</span>
                <h3 class="card-title mb-2 text-danger">{{ $stats['confirmes'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Rejetés</span>
                <h3 class="card-title mb-2 text-success">{{ $stats['rejetes'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Tentatives de Contournement</h5>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.contournements.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (ID, prestataire)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="paiement_direct" {{ request('type') == 'paiement_direct' ? 'selected' : '' }}>Paiement direct</option>
                        <option value="partage_numero" {{ request('type') == 'partage_numero' ? 'selected' : '' }}>Partage numéro</option>
                        <option value="prix_non_enregistre" {{ request('type') == 'prix_non_enregistre' ? 'selected' : '' }}>Prix non enregistré</option>
                        <option value="communication_hors_app" {{ request('type') == 'communication_hors_app' ? 'selected' : '' }}>Communication hors app</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="detecte" {{ request('statut') == 'detecte' ? 'selected' : '' }}>Détecté</option>
                        <option value="confirme" {{ request('statut') == 'confirme' ? 'selected' : '' }}>Confirmé</option>
                        <option value="rejete" {{ request('statut') == 'rejete' ? 'selected' : '' }}>Rejeté</option>
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
                        <th>Prestataire</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contournements as $contournement)
                    <tr>
                        <td>#{{ $contournement->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <img src="{{ $contournement->prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $contournement->prestataire->user->name }}</h6>
                                    <small class="text-muted">{{ $contournement->prestataire->metier }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-danger">
                                {{ ucfirst(str_replace('_', ' ', $contournement->type)) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ Str::limit($contournement->description, 50) }}</small>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'detecte' => 'warning',
                                    'confirme' => 'danger',
                                    'rejete' => 'success',
                                ];
                                $statusColor = $statusColors[$contournement->statut] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $statusColor }}">{{ ucfirst($contournement->statut) }}</span>
                        </td>
                        <td>{{ $contournement->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.contournements.show', $contournement) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                @if($contournement->statut === 'detecte')
                                    <form action="{{ route('admin.contournements.validate', $contournement) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('⚠️ Confirmer ce contournement ? Les sanctions seront appliquées automatiquement.')">
                                            <i class="bx bx-check"></i> Confirmer
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.contournements.reject', $contournement) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Rejeter ce contournement (faux positif) ?')">
                                            <i class="bx bx-x"></i> Rejeter
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Aucune tentative de contournement détectée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $contournements->links() }}
        </div>
    </div>
</div>
@endsection

